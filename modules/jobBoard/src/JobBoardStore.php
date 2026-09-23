<?php
namespace Modules\JobBoard;

interface JobBoardStore {
    public function createJobOffer(string $jobOfferTitle): int;

    public function clickJobOffer(int $jobOfferId): void;

    public function jobOfferClicks(int $jobOfferId): int;
}
