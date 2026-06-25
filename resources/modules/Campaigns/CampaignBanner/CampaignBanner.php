<?php
namespace View\Modules\Campaigns\CampaignBanner;

readonly class CampaignBanner {
    public function __construct(
        public string $redirectUrl,
        public string $imageUrl,
    ) {}
}
