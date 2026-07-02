<?php
namespace Modules\Campaigns\Internal;

/**
 * @deprecated
 */
readonly class CampaignBanners {
    /**
     * @param CampaignBanner[] $horizontal
     */
    public function __construct(
        public array           $horizontal,
        public ?CampaignBanner $sidebar,
    ) {}
}
