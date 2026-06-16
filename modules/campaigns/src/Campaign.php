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

    /**
     * @return CampaignBanner[]
     */
    public function bannersOfType(string $bannerType): array {
        return \array_filter($this->banners(),
            fn(CampaignBanner $banner) => $banner->bannerType === $bannerType);
    }

    /**
     * @return CampaignBanner[]
     */
    private function banners(): array {
        return \array_map($this->banner(...), $this->variants);
    }

    private function banner(CampaignVariant $variant): CampaignBanner {
        return new CampaignBanner(
            $variant->bannerUrl,
            $this->campaignKey,
            $variant->bannerType);
    }
}
