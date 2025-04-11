<?php
namespace Coyote\Providers\Neon;

use Carbon\Carbon;
use Coyote;
use Coyote\Domain\Settings\UserTheme;
use Coyote\Domain\StringHtml;
use Coyote\Job;
use Coyote\Job\Location;
use Coyote\Services\UrlBuilder;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Neon;
use Neon2\JobBoard;
use Neon2\JobBoard\JobBoardGate;
use Neon2\JobBoardView;
use Neon2\Payment\PaymentGate;
use Neon2\Payment\PaymentService;
use Neon2\Payment\PaymentStatus;
use Neon2\Payment\Provider\TestPaymentProvider;
use Neon2\Payment\Provider\TestPaymentWebhook;

class ServiceProvider extends RouteServiceProvider {
    public function loadRoutes(): void {
        $this->get('/events', [
            'uses' => fn() => redirect('https://wydarzenia.4programmers.net/'),
        ]);
        $this->middleware(['web', 'geocode'])->group(function () {
            $this
                ->name('neon.jobOffer.list')
                ->get('/Praca', function (UserTheme $theme): View {
                    $neon = new Neon\NeonApplication('/neon');
                    $repository = app(JobElasticSearchRepository::class);
                    foreach ($repository->jobOffers() as $jobOffer) {
                        $neon->addJobOffer($this->jobOffer($jobOffer));
                    }
                    return view('job.home_modern', [
                        'neonHead' => new StringHtml($neon->htmlMarkupHead()),
                        'neonBody' => new StringHtml($neon->htmlMarkupBody($theme->isThemeDark() ? Neon\Theme::Dark : Neon\Theme::Light)),
                    ]);
                });
        });
        $payments = new PaymentGate();
        $jobBoardGate = new JobBoardGate();
        $board = new JobBoard($payments, $jobBoardGate);
        $provider = new TestPaymentProvider();
        $webhook = new TestPaymentWebhook($provider);
        $paymentService = new PaymentService($payments, $provider);
        $this->middleware(['web'])->group(function () use ($payments, $webhook, $paymentService, $board, $jobBoardGate) {
            $this->get('/Neon', function (JobBoardView $jobBoardView): string {
                Gate::authorize('alpha-access');
                return $jobBoardView->jobBoardView();
            });
            $this->group(['prefix' => '/neon2'], function () use ($payments, $webhook, $paymentService, $board, $jobBoardGate) {
                $this->post('/job-offers', function () use ($board): JsonResponse {
                    return response()->json(
                        $board->addJobOffer(
                            request()->get('jobOfferTitle'),
                            request()->get('jobOfferPlan')),
                        status:201);
                });
                $this->post('/job-offers/payment', function () use ($paymentService) {
                    $payment = $paymentService->preparePayment(2000, request()->get('paymentId'));
                    return response()->json([
                        'providerReady' => $payment->providerReady,
                        'paymentId'     => $payment->paymentId,
                        'paymentToken'  => $payment->paymentToken,
                    ]);
                });
                $this->patch('/job-offers', function () use ($jobBoardGate): Response {
                    $jobBoardGate->editJobOffer(
                        request()->get('jobOfferId'),
                        request()->get('jobOfferTitle'));
                    return response(status:201);
                });
                $this->post('/webhook', function () use ($webhook, $board) {
                    $paymentUpdate = $webhook->acceptPaymentUpdate(
                        \file_get_contents('php://input'),
                        $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '');
                    if ($paymentUpdate) {
                        $board->paymentUpdate($paymentUpdate);
                    }
                });
                $this->get('/status', function () use ($payments) {
                    $status = $payments->readPaymentStatus(request()->query->get('paymentId'));
                    return response()->json(match ($status) {
                        PaymentStatus::Completed => 'paymentComplete',
                        PaymentStatus::Failed    => 'paymentFailed',
                        PaymentStatus::Awaiting  => 'awaitingPayment',
                    });
                });
            });
        });
    }

    private function jobOffer(Coyote\Job $jobOffer): Neon\JobOffer {
        return new Neon\JobOffer(
            $jobOffer->title,
            UrlBuilder::job($jobOffer, true),
            $jobOffer->boost_at->format('Y-m-d'),
            $this->jobOfferSalary($jobOffer),
            $this->workMode($jobOffer),
            $jobOffer->locations
                ->map(fn(Location $location): string => $location->city)
                ->filter(fn(string $city) => !empty($city))
                ->toArray(),
            $jobOffer->firm->name,
            $this->jobOfferLogoUrl($jobOffer),
            $this->jobOfferTags($jobOffer),
            $this->jobOfferLegalForm($jobOffer),
            $this->isSubscribed($jobOffer),
            $this->isMine($jobOffer),
            $this->isPromoted($jobOffer),
            $this->isNew($jobOffer),
            $this->workExperience($jobOffer));
    }

    private function workMode(Job $jobOffer): Neon\WorkMode {
        if (!$jobOffer->is_remote) {
            return Neon\WorkMode::Stationary;
        }
        if ($jobOffer->remote_range === 100) {
            return Neon\WorkMode::FullyRemote;
        }
        return Neon\WorkMode::Hybrid;
    }

    /**
     * @return string[]
     */
    private function jobOfferTags(Coyote\Job $jobOffer): array {
        return $jobOffer->tags
            ->map(fn(Coyote\Tag $tag) => $tag->real_name ?? $tag->name)
            ->toArray();
    }

    private function jobOfferLegalForm(Coyote\Job $jobOffer): Neon\LegalForm {
        return match ($jobOffer->employment) {
            'employment' => Neon\LegalForm::EmploymentContract,
            'mandatory'  => Neon\LegalForm::ContractOfMandate,
            'contract'   => Neon\LegalForm::ContractForSpecificTask,
            'b2b'        => Neon\LegalForm::BusinessToBusiness,
        };
    }

    private function jobOfferLogoUrl(Job $jobOffer): ?string {
        if ($jobOffer->firm->logo->getFilename() === null) {
            return null;
        }
        return (string)($jobOffer->firm->logo->url());
    }

    private function jobOfferSalary(Job $jobOffer): ?Neon\Salary {
        if ($jobOffer->salary_from === null && $jobOffer->salary_to === null) {
            return null;
        }
        return new Neon\Salary(
            $jobOffer->salary_from ?? $jobOffer->salary_to,
            $jobOffer->salary_to ?? $jobOffer->salary_from,
            Neon\Currency::from($jobOffer->currency->name),
            Neon\Rate::from($jobOffer->rate),
            !$jobOffer->is_gross);
    }

    private function isSubscribed(Job $jobOffer): bool {
        if (auth()->check()) {
            return $jobOffer->subscribers()->forUser(auth()->user()->id)->exists();
        }
        return false;
    }

    private function isMine(Job $jobOffer): bool {
        if (auth()->check()) {
            return $jobOffer->user_id === auth()->user()->id;
        }
        return false;
    }

    private function isPromoted(Job $jobOffer): bool {
        return $jobOffer->is_ads;
    }

    private function workExperience(Job $jobOffer): ?Neon\WorkExperience {
        return match ($jobOffer->seniority) {
            'student' => Neon\WorkExperience::Intern,
            'junior'  => Neon\WorkExperience::Junior,
            'mid'     => Neon\WorkExperience::MidLevel,
            'senior'  => Neon\WorkExperience::Senior,
            'lead'    => Neon\WorkExperience::Lead,
            'manager' => Neon\WorkExperience::Manager,
            null      => Neon\WorkExperience::NotProvided,
        };
    }

    private function isNew(Job $jobOffer): bool {
        return new \Carbon\Carbon($jobOffer->boost_at)->diffInDays(Carbon::now()) <= 2;
    }
}
