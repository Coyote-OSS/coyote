<?php
namespace Modules\Campaigns;

interface ForPresentingBanners {
    public function bannerSet(): CampaignBannerSet;

    public function recordViews(CampaignBannerSet $bannerSet): void;
}
