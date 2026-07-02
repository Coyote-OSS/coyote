<?php
namespace Test\Modules\Campaigns;

use Modules\Campaigns;
use Modules\Campaigns\Internal\CampaignBanner;
use PHPUnit\Framework\Assert;

/**
 * @deprecated
 */
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

    public function getSidebarBannerUrl(): ?string {
        return $this->sidebarBanner()->bannerUrl;
    }

    /**
     * @return string[]
     * @deprecated
     */
    public function getHorizontalCampaignKeys(): array {
        return \array_map(
            fn(CampaignBanner $banner): string => $banner->campaignKey,
            $this->horizontalBanners());
    }

    /**
     * @deprecated
     */
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
        $this->createVariant($campaignId, $horizontalBanner, Campaigns\VariantType::Standard);
        $this->createVariant($campaignId, $sidebarBanner, Campaigns\VariantType::Sidebar);
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

    public function createVariant(int $campaignId, ?string $banner, Campaigns\VariantType $type): void {
        Assert::assertNotNull($this->store->createVariant($campaignId,
            new Campaigns\Store\VariantPayload(
                $type,
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
