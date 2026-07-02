<?php
namespace Modules\Campaigns;

use Modules\Campaigns\New\CampaignBannerSet;

interface ForPresentingBanners {
    public function bannerSet(): CampaignBannerSet;

    public function recordViews(CampaignBannerSet $bannerSet): void;
}
