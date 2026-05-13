<?php
namespace Test\Modules\Campaigns;

use Modules\Campaigns;
use Modules\Campaigns\CampaignBanner;

readonly class CampaignsFacade {
    public function __construct(private Campaigns\Campaigns $campaigns) {}

    /**
     * @return string[]
     */
    public function getHorizontalBanners(): array {
        return \array_map(
            fn(CampaignBanner $banner): string => $banner->bannerUrl,
            $this->horizontalBanners());
    }

    /**
     * @return string[]
     */
    public function getHorizontalCampaignKeys(): array {
        return \array_map(
            fn(CampaignBanner $banner): string => $banner->campaignKey,
            $this->horizontalBanners());
    }

    public function getSidebarBanner(): ?string {
        return $this->sidebarBanner()->bannerUrl;
    }

    public function getSidebarCampaignKey(): ?string {
        return $this->sidebarBanner()->campaignKey;
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

    /**
     * @return CampaignBanner[]
     */
    private function horizontalBanners(): array {
        return $this->campaigns->campaignBanners()->horizontal;
    }

    private function sidebarBanner(): ?CampaignBanner {
        return $this->campaigns->campaignBanners()->sidebar;
    }
}
