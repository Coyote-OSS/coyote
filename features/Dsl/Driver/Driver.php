<?php
namespace Features\Dsl\Driver;

interface Driver {
    public function createCampaign(string $campaign, bool $premium): void;

    public function addVariant(string $campaign, string $variantType, string $variantUrl): void;

    public function resolveVariantsForUser(string $deviceType): void;

    public function variantsForSlot(string $slotType): array;
}
