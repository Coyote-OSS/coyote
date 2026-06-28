<?php
namespace Modules\Campaigns;

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

    /**
     * @return CampaignBanner[]
     */
    public function all(): array {
        if ($this->sidebar === null) {
            return $this->horizontal;
        }
        return [...$this->horizontal, $this->sidebar];
    }
}
