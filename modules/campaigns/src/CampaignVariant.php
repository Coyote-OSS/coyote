<?php
namespace Modules\Campaigns;

readonly class CampaignVariant {
    public function __construct(
        public string $bannerUrl,
        public string $bannerType,
    ) {}
}
