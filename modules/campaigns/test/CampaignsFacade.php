<?php
namespace Test\Modules\Campaigns;

use Modules\Campaigns;
use Modules\Campaigns\CampaignBanner;
use PHPUnit\Framework\Assert;

readonly class CampaignsFacade {
    public function __construct(
        private Campaigns\CampaignService      $campaigns,
        private Campaigns\Store\CampaignsStore $store,
    ) {}

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

    /**
     * @deprecated
     */
    public function addCampaign(
        ?string $sidebarBanner = null,
        ?string $horizontalBanner = null,
        ?string $name = null,
        ?string $redirectUrl = null,
        ?string $since = null,
        ?string $until = null,
    ): int {
        $campaignId = $this->createCampaign($name, $redirectUrl, $since, $until);
        $this->createVariant($campaignId, $sidebarBanner, 'sidebar');
        $this->createVariant($campaignId, $horizontalBanner, 'horizontal');
        return $campaignId;
    }

    public function createCampaign(
        ?string $name = null,
        ?string $redirectUrl = null,
        ?string $since = null,
        ?string $until = null,
    ): int {
        return $this->store->createCampaign(new Campaigns\Store\CampaignPayload(
            $name ?? '',
            $redirectUrl ?? '',
            $since ?? '1970-01-01T00:00:00',
            $until ?? '2999-12-31T23:59:59',
            999));
    }

    public function createVariant(int $campaignId, ?string $banner, string $bannerType): void {
        Assert::assertNotNull($this->store->createVariant($campaignId,
            new Campaigns\Store\VariantPayload(
                $bannerType,
                $banner ?? 'example-variant-image-url')));
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
