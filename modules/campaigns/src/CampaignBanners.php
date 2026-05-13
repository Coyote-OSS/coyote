<?php
namespace Modules\Campaigns;

readonly class CampaignBanners {
    /**
     * @param string[] $horizontal
     */
    public function __construct(
        public array   $horizontal,
        public ?string $sidebar,
        public ?string $sidebarCampaignKey,
    ) {}
}
