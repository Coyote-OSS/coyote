<?php
namespace Modules\Campaigns;

readonly class Campaign {
    public function __construct(
        public string  $campaignKey,
        public string  $sidebarBanner,
        public string  $horizontalBanner,
        public string  $redirectUrl,
        public ?string $activeSince,
        public ?string $activeUntil,
        public ?int    $targetViews,
    ) {}
}
