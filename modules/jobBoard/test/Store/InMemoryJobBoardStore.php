<?php
namespace Test\Modules\JobBoard\Store;

use Modules\JobBoard\JobBoardStore;

class InMemoryJobBoardStore implements JobBoardStore {
    private array $clicks = [];
    private array $exposures = [];
    private int $jobBoardIdSeq = 0;

    public function createJobOffer(string $jobOfferTitle): int {
        $jobBoardId = $this->jobBoardIdSeq++;
        $this->clicks[$jobBoardId] = 0;
        $this->exposures[$jobBoardId] = 0;
        return $jobBoardId;
    }

    public function clickJobOffer(int $jobOfferId): void {
        $this->clicks[$jobOfferId]++;
    }

    public function jobOfferClicks(int $jobOfferId): int {
        return $this->clicks[$jobOfferId];
    }

    public function exposeJobOffer(int $jobOfferId): void {
        $this->exposures[$jobOfferId]++;
    }

    public function jobOfferExposures(int $jobOffer): int {
        return $this->exposures[$jobOffer];
    }
}
