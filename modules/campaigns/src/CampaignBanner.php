<?php
namespace Modules\Campaigns;

readonly class CampaignBanner {
    public function __construct(
        public string $bannerUrl,
        public string $campaignKey,
        public string $bannerType,
        public int    $variantId,
    ) {}
}
