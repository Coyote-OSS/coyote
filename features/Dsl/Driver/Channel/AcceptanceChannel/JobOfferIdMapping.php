<?php
namespace Features\Dsl\Driver\Channel\AcceptanceChannel;

class JobOfferIdMapping {
    /** @var array<string, int> */
    private array $ids = [];

    public function setJobOfferId(string $jobOffer, int $jobOfferId): void {
        if (\array_key_exists($jobOffer, $this->ids)) {
            throw new \Exception("Job offer already has an id assigned: $jobOffer");
        }
        $this->ids[$jobOffer] = $jobOfferId;
    }

    public function getJobOfferId(string $jobOffer): int {
        return $this->ids[$jobOffer] ?? throw new \Exception(
            "Job offer has not been assigned an id: $jobOffer",
        );
    }
}
