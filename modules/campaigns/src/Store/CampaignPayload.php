<?php
namespace Modules\Campaigns\Store;

use Modules\Campaigns\Voivodeship;

readonly class CampaignPayload {
    public function __construct(
        public ?string      $name,
        public string       $redirectUrl,
        public ?string      $activeSinceDate,
        public ?string      $activeUntilDate,
        public ?int         $targetViews,
        public ?string      $description,
        public bool         $isPremium,
        public ?Voivodeship $voivodeship,
    ) {}
}
