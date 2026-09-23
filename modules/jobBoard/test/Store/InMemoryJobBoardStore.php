<?php
namespace Test\Modules\JobBoard\Store;

use Modules\JobBoard\JobBoardStore;

class InMemoryJobBoardStore implements JobBoardStore {
    private array $clicks = [];
    private int $jobBoardIdSeq = 0;

    public function createJobOffer(string $jobOfferTitle): int {
        $jobBoardId = $this->jobBoardIdSeq++;
        $this->clicks[$jobBoardId] = 0;
        return $jobBoardId;
    }

    public function clickJobOffer(int $jobOfferId): void {
        $this->clicks[$jobOfferId]++;
    }

    public function jobOfferClicks(int $jobOfferId): int {
        return $this->clicks[$jobOfferId];
    }
}
