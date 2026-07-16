<?php
namespace Modules\Campaigns;

interface ForCampaignBanners {
    public function bannerSet(): CampaignBannerSet;

    public function recordViews(CampaignBannerSet $bannerSet): void;
}
