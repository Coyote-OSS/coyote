<?php
namespace Modules\Campaigns;

readonly class CampaignPayload {
    public function __construct(
        public ?string $name,
        public string  $redirectUrl,
        public ?string $activeSinceDate,
        public ?string $activeUntilDate,
        public ?int    $activeBelowViews,
    ) {}
}
