<?php
namespace Modules\Campaigns;

use Libs\Arrays\arrays;

readonly class CampaignBannersFacade implements ForCampaignBanners {
    public function __construct(
        private CampaignService $service,
        private ForRedirectUrls $redirectUrls,
    ) {}

    public function bannerSet(DeviceType $device): CampaignBannerSet {
        $banners = $this->service->campaignBanners($device);
        return new CampaignBannerSet(
            $banners->horizontal |> arrays::map($this->mapBanner(...)),
            $banners->sidebar !== null ? $this->mapBanner($banners->sidebar) : null,
            $banners->feed |> arrays::map($this->mapBanner(...)),
        );
    }

    private function mapBanner(Internal\CampaignBanner $banner): CampaignBanner {
        return new CampaignBanner(
            $this->redirectUrls->redirectUrl($banner->variantId),
            $this->redirectUrls->exposeUrl($banner->variantId),
            $this->redirectUrls->adblockUrl($banner->variantId),
            $banner->bannerUrl,
            $banner->variantId);
    }
}
