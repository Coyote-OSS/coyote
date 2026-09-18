<?php
namespace Modules\Campaigns\Internal;

/**
 * @deprecated
 */
readonly class CampaignBanners {
    /**
     * @param CampaignBanner[] $horizontal
     * @param CampaignBanner[] $feed
     */
    public function __construct(
        public array           $horizontal,
        public ?CampaignBanner $sidebar,
        public array           $feed,
    ) {}
}
