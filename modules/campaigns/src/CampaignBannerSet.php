<?php
namespace Modules\Campaigns;

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
