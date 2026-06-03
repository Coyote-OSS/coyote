<?php
namespace Modules\Campaigns;

readonly class Campaign {
    /**
     * @deprecated
     */
    public static function create(
        string  $campaignKey,
        string  $sidebarBanner,
        string  $horizontalBanner,
        string  $redirectUrl,
        ?string $activeSince,
        ?string $activeUntil,
        ?int    $targetViews,
    ): self {
        return new self(
            $campaignKey,
            $sidebarBanner,
            $horizontalBanner,
            $redirectUrl,
            $activeSince,
            $activeUntil,
            $targetViews,
            []);
    }

    /**
     * @param CampaignVariant[] $variants
     */
    public function __construct(
        public string  $campaignKey,
        public string  $sidebarBanner,
        public string  $horizontalBanner,
        public string  $redirectUrl,
        public ?string $activeSince,
        public ?string $activeUntil,
        public ?int    $targetViews,
        private array  $variants,
    ) {}

    public function horizontalBanner(): string {
        return $this->horizontalBanner;
    }

    public function sidebarBanner(): string {
        return $this->sidebarBanner;
    }
}
