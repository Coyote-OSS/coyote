<?php
namespace Coyote\Modules\JobBoard\Eloquent;

use Carbon\Carbon;
use Coyote;
use Coyote\Modules\JobBoard\Eloquent;
use Modules\JobBoard\JobBoardStore;

class EloquentJobBoardStore implements JobBoardStore {
    public function createJobOffer(string $jobOfferTitle): int {
        return Eloquent\JobOffer::query()
            ->create([
                'user_id'     => $this->jobOfferOwnerId(),
                'title'       => $jobOfferTitle,
                'slug'        => 'slug',
                'deadline_at' => new Carbon(),
                'clicks'      => 0,
            ])
            ->id;
    }

    public function clickJobOffer(int $jobOfferId): void {
        Eloquent\JobOffer::query()->whereKey($jobOfferId)->increment('clicks');
    }

    public function jobOfferClicks(int $jobOfferId): int {
        return Eloquent\JobOffer::query()->findOrFail($jobOfferId)->clicks;
    }

    private function jobOfferOwnerId(): int {
        $user = new Coyote\User();
        $user->name = 'job-offer-owner-' . \uniqId();
        $user->email = 'job-offer-owner';
        $user->save();
        return $user->id;
    }
}
