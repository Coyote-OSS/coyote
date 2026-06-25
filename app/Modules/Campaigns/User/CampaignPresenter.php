<?php
namespace Coyote\Modules\Campaigns\User;

use Modules\Campaigns as Domain;
use View\Modules\Campaigns\CampaignBanner as View;
use View\Modules\Campaigns\CampaignBanner\CampaignBannerSet;

class CampaignPresenter {
    public function bannerSet(Domain\CampaignBanners $banners): View\CampaignBannerSet {
        return new CampaignBannerSet(
            \array_map($this->viewCampaignBanner(...), $banners->horizontal),
            $banners->sidebar === null
                ? $banners->sidebar
                : $this->viewCampaignBanner($banners->sidebar));
    }

    private function viewCampaignBanner(Domain\CampaignBanner $banner): View\CampaignBanner {
        return new View\CampaignBanner(
            route('campaigns.click', [$banner->variantId]),
            $banner->bannerUrl,
        );
    }
}
