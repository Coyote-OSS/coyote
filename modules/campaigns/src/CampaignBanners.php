<?php
namespace Modules\Campaigns;

use Modules\Campaigns\Store\CampaignsStore;

readonly class CampaignBanners {
    /**
     * @param CampaignBanner[] $horizontal
     */
    public function __construct(
        public array           $horizontal,
        public ?CampaignBanner $sidebar,
    ) {}

    public function visitVariants(CampaignsStore $store): void {
        if ($this->sidebar) {
            $store->viewVariant($this->sidebar->variantId);
        }
        foreach ($this->horizontal as $banner) {
            $store->viewVariant($banner->variantId);
        }
    }
}
