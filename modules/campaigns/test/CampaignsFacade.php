<?php
namespace Test\Modules\Campaigns;

use Modules\Campaigns;

readonly class CampaignsFacade {
    public function __construct(private Campaigns\Campaigns $campaigns) {}

    /**
     * @return string[]
     */
    public function getHorizontalBanners(): array {
        return \array_map(
            fn(Campaigns\CampaignBanner $banner): string => $banner->bannerUrl,
            $this->campaigns->campaignBanners()->horizontal);
    }

    public function getSidebarBanner(): ?string {
        return $this->campaigns->campaignBanners()->sidebar->bannerUrl;
    }

    public function getSidebarCampaignKey(): ?string {
        return $this->campaigns->campaignBanners()->sidebar->campaignKey;
    }

    public function addCampaign(
        ?string $sidebarBanner = null,
        ?string $horizontalBanner = null,
        ?string $campaignKey = null,
        ?string $redirectUrl = null,
    ): void {
        $this->campaigns->add(
            $sidebarBanner ?? '',
            $horizontalBanner ?? '',
            $campaignKey ?? '',
            $redirectUrl ?? '');
    }
}
