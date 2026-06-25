<?php
namespace Modules\Campaigns;

readonly class CampaignBanner {
    public function __construct(
        public string      $bannerUrl,
        public string      $campaignKey,
        public VariantType $type,
        public int         $variantId,
    ) {}

    public function bannerType(): string {
        return match ($this->type) {
            VariantType::Horizontal => 'horizontal',
            VariantType::Sidebar    => 'sidebar',
        };
    }
}
