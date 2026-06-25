<?php
namespace View\Modules\Campaigns\CampaignBanner;

readonly class CampaignBannerSet {
    /**
     * @param CampaignBanner[] $horizontal
     * @param CampaignBanner|null $sidebar
     */
    public function __construct(
        public array           $horizontal,
        public ?CampaignBanner $sidebar,
    ) {}
}
