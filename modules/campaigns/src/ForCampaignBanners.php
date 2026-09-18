<?php
namespace Modules\Campaigns;

interface ForCampaignBanners {
    public function bannerSet(DeviceType $device): CampaignBannerSet;
}
