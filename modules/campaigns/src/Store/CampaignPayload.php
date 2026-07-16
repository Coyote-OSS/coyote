<?php
namespace Modules\Campaigns\Store;

readonly class CampaignPayload {
    public function __construct(
        public ?string $name,
        public string  $redirectUrl,
        public ?string $activeSinceDate,
        public ?string $activeUntilDate,
        public ?int    $targetViews,
        public ?string $description,
        public bool    $isPremium,
    ) {}
}
