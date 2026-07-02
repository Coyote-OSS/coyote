<?php
namespace Modules\Campaigns;

use Libs\Arrays\arrays;
use Modules\Campaigns\Internal\CampaignBanner;
use Modules\Campaigns\Store\CampaignsStore;

readonly class CampaignBannersPresenter implements ForPresentingBanners {
    public function __construct(
        private CampaignService $service,
        private CampaignsStore  $store,
        private ForRedirectUrls $redirectUrls,
    ) {}

    public function bannerSet(): CampaignBannerSet {
        $banners = $this->service->campaignBanners();
        return new CampaignBannerSet(
            $banners->horizontal |> arrays::map($this->mapBanner(...)),
            $banners->sidebar !== null ? $this->mapBanner($banners->sidebar) : null,
        );
    }

    public function recordViews(CampaignBannerSet $bannerSet): void {
        foreach ($bannerSet->horizontal as $banner) {
            $this->store->viewVariant($banner->variantId);
        }
        if ($bannerSet->sidebar !== null) {
            $this->store->viewVariant($bannerSet->sidebar->variantId);
        }
    }

    private function mapBanner(CampaignBanner $banner): \Modules\Campaigns\CampaignBanner {
        return new \Modules\Campaigns\CampaignBanner(
            $this->redirectUrls->redirectUrl($banner->variantId),
            $banner->bannerUrl,
            $banner->variantId);
    }
}
