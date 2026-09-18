<?php
namespace Modules\Campaigns;

readonly class CampaignBannerSet {
    /**
     * @param CampaignBanner[] $horizontal
     * @param CampaignBanner|null $sidebar
     * @param CampaignBanner[] $feed
     */
    public function __construct(
        public array           $horizontal,
        public ?CampaignBanner $sidebar,
        public array           $feed,
    ) {}
}
