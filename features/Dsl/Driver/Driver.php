<?php
namespace Features\Dsl\Driver;

interface Driver {
    public function initialize(string $feature, string $scenario): void;

    public function finalize(): void;

    public function captureDiagnostics(string $testTitle);

    public function createCampaign(string $campaign, bool $premium): void;

    public function addVariant(string $campaign, string $variantType, string $variantUrl): void;

    public function resolveVariantsForUser(string $deviceType): void;

    public function variantsForSlot(string $slotType): array;

    public function createJobOffer(string $jobOffer): void;

    public function clickJobOffer(string $jobOffer): void;

    public function jobOfferClicks(string $jobOffer): int;
}
