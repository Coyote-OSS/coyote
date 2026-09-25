<?php
namespace Coyote\Modules\JobBoard\Eloquent;

use Carbon\Carbon;
use Coyote;
use Coyote\Modules\JobBoard\Eloquent;
use Modules\JobBoard\JobBoardStore;

class EloquentJobBoardStore implements JobBoardStore {
    public function createJobOffer(string $jobOfferTitle): int {
        $freePlan = $this->freePlan();
        $ownerId = $this->jobOfferOwnerId();
        return Eloquent\JobOffer::query()
            ->create([
                'user_id'     => $ownerId,
                'firm_id'     => $this->jobOfferFirmId($ownerId),
                'plan_id'     => $freePlan->id,
                'is_publish'  => true,
                'title'       => $jobOfferTitle,
                'slug'        => 'slug',
                'deadline_at' => Carbon::now()->addDays($freePlan->length),
                'clicks'      => 0,
                'exposures'   => 0,
            ])
            ->id;
    }

    public function clickJobOffer(int $jobOfferId): void {
        Eloquent\JobOffer::query()->whereKey($jobOfferId)->increment('clicks');
    }

    public function jobOfferClicks(int $jobOfferId): int {
        return Eloquent\JobOffer::query()->findOrFail($jobOfferId)->clicks;
    }

    public function removeJobOffers(): void {
        Coyote\Payment::query()->whereIn('job_id', Eloquent\JobOffer::query()->select('id'))->delete();
        Eloquent\JobOffer::query()->delete();
    }

    private function freePlan(): Coyote\Plan {
        return Coyote\Plan::query()->where('name', 'Free')->firstOrFail();
    }

    private function jobOfferOwnerId(): int {
        $user = new Coyote\User();
        $user->name = 'job-offer-owner-' . \uniqId();
        $user->email = 'job-offer-owner';
        $user->save();
        return $user->id;
    }

    private function jobOfferFirmId(int $ownerId): int {
        return Coyote\Firm::query()
            ->forceCreate([
                'user_id' => $ownerId,
                'name'    => 'job-offer-firm',
            ])
            ->id;
    }

    public function exposeJobOffer(int $jobOfferId): void {
        Eloquent\JobOffer::query()->whereKey($jobOfferId)->increment('exposures');
    }

    public function jobOfferExposures(int $jobOffer): int {
        return Eloquent\JobOffer::query()->findOrFail($jobOffer)->exposures;
    }
}
