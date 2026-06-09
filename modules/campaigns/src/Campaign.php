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
            $redirectUrl,
            $activeSince,
            $activeUntil,
            $targetViews,
            [
                new CampaignVariant($sidebarBanner, 'sidebar'),
                new CampaignVariant($horizontalBanner, 'horizontal'),
            ]);
    }

    /**
     * @param CampaignVariant[] $variants
     */
    public function __construct(
        public string  $campaignKey,
        public string  $redirectUrl,
        public ?string $activeSince,
        public ?string $activeUntil,
        public ?int    $targetViews,
        public array   $variants,
    ) {}
}
