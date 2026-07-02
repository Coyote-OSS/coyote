<?php
namespace Modules\Campaigns;

use Modules\Campaigns\New\CampaignBannerSet;
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
            \array_map($this->mapBanner(...), $banners->horizontal),
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

    private function mapBanner(CampaignBanner $banner): New\CampaignBanner {
        return new New\CampaignBanner(
            $this->redirectUrls->redirectUrl($banner->variantId),
            $banner->bannerUrl,
            $banner->variantId);
    }
}
