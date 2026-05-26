<?php
namespace Test\Modules\Campaigns;

use Modules\Campaigns;
use Modules\Campaigns\CampaignBanner;

readonly class CampaignsFacade {
    public function __construct(private Campaigns\CampaignService $campaigns) {}

    /**
     * @return string[]
     */
    public function getHorizontalBannerUrls(): array {
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

    public function getSidebarBannerUrl(): ?string {
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
        ?string $since = null,
        ?string $until = null,
    ): void {
        $this->campaigns->add(
            $sidebarBanner ?? '',
            $horizontalBanner ?? '',
            $campaignKey ?? '',
            $redirectUrl ?? '',
            $since ?? '1970-01-01T00:00:00', // canonical catch-all since date
            $until ?? '2999-12-31T23:59:59', // canonical catch-all until date
            999, // canonica catch-all target views
        );
    }

    /**
     * @return CampaignBanner[]
     */
    public function horizontalBanners(): array {
        return $this->campaigns->campaignBanners()->horizontal;
    }

    public function sidebarBanner(): ?CampaignBanner {
        return $this->campaigns->campaignBanners()->sidebar;
    }
}
