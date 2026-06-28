<?php
namespace Modules\Campaigns\New;

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
