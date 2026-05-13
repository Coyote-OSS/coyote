<?php
namespace Modules\Campaigns;

readonly class CampaignBanners {
    /**
     * @param CampaignBanner[] $horizontal
     */
    public function __construct(
        public array           $horizontal,
        public ?CampaignBanner $sidebar,
    ) {}
}
