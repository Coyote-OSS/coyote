<?php
namespace Coyote\Services;

use Carbon\Carbon;
use Coyote\Events\JobWasSaved;
use Coyote\Feature;
use Coyote\Job;
use Coyote\Listeners\BoostJobOffer;
use Coyote\Models\UserPlanBundle;
use Coyote\Payment;
use Coyote\Repositories\Eloquent\CouponRepository;
use Coyote\Repositories\Eloquent\FirmRepository;
use Coyote\Repositories\Eloquent\JobRepository;
use Coyote\Services\Stream\Activities\Create as Stream_Create;
use Coyote\Services\Stream\Activities\Update as Stream_Update;
use Coyote\Services\Stream\Objects\Job as Stream_Job;
use Coyote\Tag;
use Coyote\User;
use Illuminate\Database\Connection;
use Illuminate\Http\Request;

readonly class SubmitJobService {
    public function __construct(
        private JobRepository    $job,
        private FirmRepository   $firm,
        private Request          $request,
        public Connection        $connection,
        private CouponRepository $coupons,
    ) {}

    public function submitJobOffer(User $user, Job $job): void {
        $this->connection->transaction(function () use ($user, $job) {
            $this->saveRelations($job, $user,
                $this->request->input('tags', []));
            if ($job->wasRecentlyCreated || !$job->is_publish) {
                $this->redeemPlanBundleOrCreatePayment($user, $job);
            }
            event(new JobWasSaved($job)); // we don't queue listeners for this event
        });
    }

    public function redeemPlanBundleOrCreatePayment(User $user, Job $job): void {
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
        $this->createJobPayment($user, $job);
    }

    public function createJobPayment(User $user, Job $job): void {
        $coupon = $this->coupons->findCoupon($user->id, $job->plan->price);
        $job->payments()->create([
            'plan_id'   => $job->plan->id,
            'days'      => $job->plan->length,
            'coupon_id' => $coupon->id ?? null,
        ]);
    }

    public function getUnpaidPayment(Job $job): ?Payment {
        return !$job->is_publish ? $job->getUnpaidPayment() : null;
    }

    public function loadDefaults(Job $job, User $user): Job {
        $firm = $this->firm->loadDefaultFirm($user->id);
        $job->firm()->associate($firm);
        $job->firm->load(['benefits', 'assets']);
        $job->plan_id = request('plan');
        $job->email = $user->email;
        $job->user_id = $user->id;
        $job->setRelation('features', $this->getDefaultFeatures($job, $user));
        return $job;
    }

    public function saveRelations(Job $job, User $user, array $tagInput): Job {
        $activity = $job->id ? Stream_Update::class : Stream_Create::class;
        if ($job->firm) {
            if (!$job->firm->exists) {
                $job->firm->save();
            }
            // reassociate job with firm. user could change firm, that's why we have to do it again.
            $job->firm()->associate($job->firm);
            // remove old benefits and save new ones.
            $job->firm->benefits()->push($job->firm->benefits);
            $job->firm->assets()->sync($this->request->input('firm.assets'));
        }
        $job->creating(function (Job $model) use ($user) {
            $model->user_id = $user->id;
        });
        $job->save();
        $job->locations()->push($job->locations);
        $job->tags()->sync($this->tags($tagInput));
        $job->features()->sync($this->features($this->request->input('features', [])));
        stream($activity, (new Stream_Job)->map($job));
        return $job;
    }

    private function getDefaultFeatures(Job $job, User $user): array {
        $features = $this->job->getDefaultFeatures($user->id);
        $models = [];
        foreach ($features as $feature) {
            $checked = (int)$feature['checked'];
            $pivot = $job->features()->newPivot([
                'checked' => $checked,
                'value'   => $checked ? ($feature['value'] ?? null) : null,
            ]);
            $models[] = Feature::query()->findOrNew($feature['id'])->setRelation('pivot', $pivot);
        }
        return $models;
    }

    private function features(array $featureInput): array {
        $features = [];
        foreach ($featureInput as $feature) {
            $checked = (int)$feature['checked'];
            $features[$feature['id']] = ['checked' => $feature['checked'], 'value' => $checked ? ($feature['value'] ?? null) : null];
        }
        return $features;
    }

    private function tags(array $tagsInput): array {
        $tags = [];
        $order = 0;
        foreach ($tagsInput as $tag) {
            $model = Tag::query()->firstOrCreate(['name' => $tag['name']]);
            $tags[$model->id] = [
                'priority' => $tag['priority'] ?? 0,
                'order'    => ++$order,
            ];
        }
        return $tags;
    }
}
