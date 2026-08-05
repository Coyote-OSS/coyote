<?php
namespace Test\Modules\Campaigns\Fixture;

use Libs\Arrays\arrays;
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
        return $this->horizontalBanners() |> arrays::map(fn(CampaignBanner $banner): string => $banner->bannerUrl);
    }

    public function getSidebarBannerUrl(): ?string {
        return $this->sidebarBanner()->bannerUrl;
    }

    /**
     * @return string[]
     * @deprecated
     */
    public function getHorizontalCampaignKeys(): array {
        return $this->horizontalBanners() |> arrays::map(fn(CampaignBanner $banner): string => $banner->campaignKey);
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
        bool    $isPremium = false,
    ): int {
        return $this->store->createCampaign(new Campaigns\Store\CampaignPayload(
            $name ?? '',
            $redirectUrl ?? '',
            $since ?? '1970-01-01T00:00:00',
            $until ?? '2999-12-31T23:59:59',
            999,
            null,
            $isPremium,
            null));
    }

    public function createVariant(
        int                   $campaignId,
        ?string               $banner,
        Campaigns\VariantType $type,
        bool                  $enabled = true,
    ): void {
        $variantId = $this->createEnabledVariant($campaignId, $type, $banner);
        $this->store->setVariantEnabled($variantId, $enabled);
    }

    private function createEnabledVariant(int $campaignId, Campaigns\VariantType $type, ?string $banner): int {
        $payload = new Campaigns\Store\VariantPayload($type, $banner ?? 'example-variant-image-url');
        $variantId = $this->store->createVariant($campaignId, $payload);
        Assert::assertNotNull($variantId);
        return $variantId;
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
