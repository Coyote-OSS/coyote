<?php
namespace Coyote\Providers\Neon;

use Carbon\Carbon;
use Coyote;
use Coyote\Events\JobWasSaved;
use Coyote\Events\PaymentPaid;
use Coyote\Job;
use Coyote\Listeners\BoostJobOffer;
use Coyote\Models\UserPlanBundle;
use Coyote\Notifications\Job\CreatedNotification;
use Coyote\Payment;
use Coyote\Repositories\Criteria\EagerLoading;
use Coyote\Repositories\Criteria\Job\IncludeSubscribers;
use Coyote\Repositories\Eloquent\JobRepository;
use Coyote\Repositories\Eloquent\PaymentRepository;
use Coyote\Services\Invoice;
use Coyote\Services\Invoice\CalculatorFactory;
use Coyote\Services\Invoice\VatRateCalculator;
use Coyote\Services\SubmitJobService;
use Illuminate\Database;
use Neon2\Coyote\Integration;
use Neon2\JobBoard;
use Neon2\JobBoard\InvoiceInformation;
use Neon2\JobBoard\JobOffer;
use Neon2\JobBoard\PaymentIntent;
use Neon2\JobBoard\PlanBundle;
use Neon2\Payment\PaymentStatus;
use Neon2\Payment\PreparedPayment;
use Neon2\Payment\Provider\PaymentProvider;
use Neon2\Payment\Provider\PaymentWebhook;
use Neon2\Request\JobOfferSubmit;

readonly class CoyoteIntegration implements Integration {
    public function __construct(
        private JobOfferMapper      $mapper,
        private JobOfferUnmapper    $unmapper,
        private JobRepository       $job,
        private SubmitJobService    $submitJob,
        private Invoice\Generator   $invoice,
        private Database\Connection $database,
        private PaymentProvider     $provider,
        private PaymentWebhook      $paymentWebhook,
        private PaymentRepository   $repository,
    ) {}

    /**
     * @return JobOffer[]
     */
    public function listJobOffers(): array {
        $this->job->pushCriteria(new EagerLoading(['firm', 'firm.assets', 'locations', 'tags', 'currency']));
        $this->job->pushCriteria(new IncludeSubscribers(auth()->id()));
        return $this->job->findPublicJobOffers(auth()->id())
            ->sortBy('boost_at')
            ->sortBy('is_on_top')
            ->sortBy($this->isNew(...))
            ->sortBy('is_publish') // With my offers, not paid should be highest
            ->values()
            ->map(function (Coyote\Job $job): JobBoard\JobOffer {
                if ($this->isMine($job)) {
                    $payment = $job->getUnpaidPayment();
                    if ($payment && $payment->plan->price) {
                        return $this->neonJobOffer($job, $this->paymentIntent($payment));
                    }
                }
                return $this->neonJobOffer($job, null);
            })
            ->toArray();
    }

    public function neonJobOffer(
        Coyote\Job     $jobOffer,
        ?PaymentIntent $intent,
    ): JobBoard\JobOffer {
        return new JobBoard\JobOffer(
            $jobOffer->id,
            $jobOffer->deadline + 1,
            $this->jobOfferStatus($jobOffer),
            $this->mapper->jobOfferFields($jobOffer),
            $intent,
            $this->slug($jobOffer),
            route('job.application', [$jobOffer->id]),
            $this->canEdit($jobOffer),
            $this->isMine($jobOffer),
            $this->isNew($jobOffer));
    }

    public function planBundle(?int $userId): PlanBundle {
        if ($userId === null) {
            return PlanBundle::notOwned();
        }
        /** @var UserPlanBundle $bundle */
        $bundle = UserPlanBundle::query()
            ->where('user_id', '=', $userId)
            ->where('remaining', '>', '0')
            ->first();
        if ($bundle) {
            return PlanBundle::remaining($bundle->remaining, $bundle->plan->name);
        }
        return PlanBundle::notOwned();
    }

    public function redeemBundle(int $userId, int $jobOfferId): void {
        /** @var Coyote\User $user */
        $user = Coyote\User::query()->find($userId);
        /** @var Coyote\Job $job */
        $job = Coyote\Job::query()->find($jobOfferId);
        /** @var UserPlanBundle|null $bundle */
        $bundle = $user->planBundles()->where('plan_id', $job->plan_id)->first();
        if ($bundle) {
            if ($bundle->remaining > 0) {
                $bundle->remaining--;
                $bundle->save();
                BoostJobOffer::publishJob($job, $bundle->plan, Carbon::now()->addDays($bundle->plan->length));
                BoostJobOffer::indexJobOffer($job);
                return;
            }
            $bundle->delete();
        }
    }

    public function revokePlanBundle(int $userId): void {
        UserPlanBundle::query()->where('user_id', '=', $userId)->delete();
    }

    public function createJobOffer(string $jobOfferPlan, JobOfferSubmit $jobOffer): JobOffer {
        $user = $this->loggedUser();
        $job = new Job();
        $job->plan_id = $this->unmapper->planId($jobOfferPlan);
        $job->fill($this->unmapper->jobOfferInput($user, $jobOffer));
        $job->firm->fill($this->unmapper->companyInput($user, $jobOffer));
        $job->firm->user_id = $user->id;
        $this->submitJob->connection->transaction(function () use ($jobOffer, $user, $job) {
            $this->submitJob->saveRelations($job, $user,
                $this->unmapper->coyoteTagNames($jobOffer->tagNames, $jobOffer->tagPriorities));
            $job->firm->assets()->sync($this->unmapper->coyoteCompanyPhotoAssets($jobOffer));
            $this->submitJob->createJobPayment($user, $job);
            event(new JobWasSaved($job)); // we don't queue listeners for this event
        });
        $job->user->notify(new CreatedNotification($job));
        $payment = $job->getUnpaidPayment();
        if (!$payment->plan->price) {
            event(new PaymentPaid($payment));
            return $this->neonJobOffer($job->fresh(), null);
        }
        return $this->neonJobOffer($job, $this->paymentIntent($payment));
    }

    private function canEdit(Coyote\Job $jobOffer): bool {
        if (auth()->check()) {
            /** @var Coyote\User $user */
            $user = auth()->user();
            return $user->can('update', $jobOffer);
        }
        return false;
    }

    private function isMine(Job $jobOffer): bool {
        if (auth()->check()) {
            return $jobOffer->user_id === auth()->id();
        }
        return false;
    }

    private function loggedUser(): Coyote\User {
        if (auth()->check()) {
            /** @var Coyote\User $user */
            $user = auth()->user();
            return $user;
        }
        abort(403);
    }

    private function paymentIntent(Coyote\Payment $payment): PaymentIntent {
        $calculator = CalculatorFactory::payment($payment);
        return new PaymentIntent(
            $payment->id,
            (int)($calculator->netPrice() * 100),
            (int)($calculator->vatPrice() * 100),
            \lcFirst($payment->plan->name));
    }

    public function updateJobOffer(int $jobOfferId, JobOfferSubmit $jobOffer): void {
        $user = $this->loggedUser();
        $job = Job::query()->find($jobOfferId);
        if (!$user->can('update', $job)) {
            abort(403);
        }
        $job->fill($this->unmapper->jobOfferInput($user, $jobOffer));
        $job->firm->user_id = $user->id;
        $job->firm->fill($this->unmapper->companyInput($user, $jobOffer));
        $this->submitJob->connection->transaction(function () use ($user, $jobOffer, $job) {
            $job->firm->save();
            $job->firm->assets()->sync($this->unmapper->coyoteCompanyPhotoAssets($jobOffer));
            $this->submitJob->saveRelations($job, $user,
                $this->unmapper->coyoteTagNames($jobOffer->tagNames, $jobOffer->tagPriorities));
            event(new JobWasSaved($job)); // we don't queue listeners for this event
        });
    }

    public function preparePayment(int $userId, string $paymentId, InvoiceInformation $invoiceInfo): PreparedPayment {
        $payment = $this->findPaymentById($paymentId);
        $calculator = CalculatorFactory::payment($payment);
        $country = Coyote\Country::query()
            ->where('code', $invoiceInfo->countryCode)
            ->first();
        $calculator->vatRate = new VatRateCalculator()->vatRate($country, $invoiceInfo->vatId);
        if ($payment->job->firm_id) {
            $payment->job->firm->update([
                'invoice.vat_id'     => $invoiceInfo->vatId,
                'invoice.country_id' => $country->id,
            ]);
        }
        $this->database->transaction(function () use ($invoiceInfo, $userId, $payment, $calculator) {
            $invoice = $this->invoice->create([
                'name'        => $invoiceInfo->companyName,
                'address'     => $invoiceInfo->companyAddress,
                'postal_code' => $invoiceInfo->companyPostalCode,
                'city'        => $invoiceInfo->companyCity,
                'vat_id'      => $invoiceInfo->vatId,
                'country_id'  => $this->coyoteCountryId($invoiceInfo->countryCode),
                'user_id'     => $userId,
            ], $payment, $calculator);
            $payment->invoice()->associate($invoice);
            $payment->save();
        });
        if (!$calculator->grossPrice()) {
            abort(400);
        }
        return $this->provider->prepareCardPayment($payment->invoice->grossPrice() * 100, $payment->id);
    }

    private function coyoteCountryId(string $countryCode): int {
        return Coyote\Country::query()
            ->where('code', $countryCode)
            ->first('id')
            ->id;
    }

    public function paymentWebhook(string $webhookPayload, string $stripeSignature): void {
        $paymentUpdate = $this->paymentWebhook->acceptPaymentUpdate($webhookPayload, $stripeSignature);
        if ($paymentUpdate) {
            /** @var Coyote\Payment $payment */
            $payment = $this->repository->findOrFail($paymentUpdate->paymentId);
            if ($paymentUpdate->type === PaymentStatus::Completed) {
                event(new PaymentPaid($payment));
            }
            if ($paymentUpdate->type === PaymentStatus::Failed) {
                $payment->update(['status_id' => Payment::FAILED]);
            }
        }
    }

    public function paymentStatus(string $paymentId): PaymentStatus {
        $payment = $this->findPaymentById($paymentId);
        if ($payment->status_id === Coyote\Payment::PAID) {
            return PaymentStatus::Completed;
        }
        if ($payment->status_id === Coyote\Payment::FAILED) {
            return PaymentStatus::Failed;
        }
        return PaymentStatus::Awaiting;
    }

    private function findPaymentById(string $paymentId): Coyote\Payment {
        return Coyote\Payment::query()->find($paymentId);
    }

    public static function slug(Job $jobOffer): string {
        return \implode('-',
            \array_map(str_slug(...), self::slugPieces($jobOffer)));
    }

    private static function slugPieces(Job $jobOffer): array {
        $pieces = [$jobOffer->firm->name, $jobOffer->title];
        if ($jobOffer->locations->isNotEmpty()) {
            if (!empty($jobOffer->locations[0]->city)) {
                $pieces[] = $jobOffer->locations[0]->city;
            }
        }
        return $pieces;
    }

    private function jobOfferStatus(Job $jobOffer): JobBoard\JobOfferStatus {
        if (!$jobOffer->is_publish) {
            return JobBoard\JobOfferStatus::AwaitingPayment;
        }
        if ($jobOffer->deadline_at > Carbon::now()) {
            return JobBoard\JobOfferStatus::Published;
        }
        return JobBoard\JobOfferStatus::Expired;
    }

    private function isNew(Job $jobOffer): bool {
        return $jobOffer->created_at->diffInHours(Carbon::now()) <= 24;
    }
}
