<?php
namespace Modules\Campaigns;

use Libs\Arrays\arrays;
use Modules\Campaigns\Internal\CampaignBanner;

readonly class CampaignBannersFacade implements ForCampaignBanners {
    public function __construct(
        private CampaignService $service,
        private ForRedirectUrls $redirectUrls,
    ) {}

    public function bannerSet(): CampaignBannerSet {
        $banners = $this->service->campaignBanners();
        return new CampaignBannerSet(
            $banners->horizontal |> arrays::map($this->mapBanner(...)),
            $banners->sidebar !== null ? $this->mapBanner($banners->sidebar) : null,
        );
    }

    private function mapBanner(CampaignBanner $banner): \Modules\Campaigns\CampaignBanner {
        return new \Modules\Campaigns\CampaignBanner(
            $this->redirectUrls->redirectUrl($banner->variantId),
            $this->redirectUrls->exposeUrl($banner->variantId),
            $this->redirectUrls->adblockUrl($banner->variantId),
            $banner->bannerUrl,
            $banner->variantId);
    }
}
