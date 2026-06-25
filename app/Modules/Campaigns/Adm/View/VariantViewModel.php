<?php
namespace Coyote\Modules\Campaigns\Adm\View;

use Modules\Campaigns\VariantType;

readonly class VariantViewModel {
    public function __construct(
        public string        $imageUrl,
        public CampaignStats $stats,
        private VariantType  $type,
    ) {}

    public function bannerType(): string {
        return match ($this->type) {
            VariantType::Horizontal => 'horizontal',
            VariantType::Sidebar    => 'sidebar',
        };
    }

    public function bannerTypeTitle(): string {
        return match ($this->type) {
            VariantType::Horizontal => 'Banner',
            VariantType::Sidebar    => 'Rectangle',
        };
    }

    public function expectedDimension(): string {
        return match ($this->type) {
            VariantType::Horizontal => '728 × 90',
            VariantType::Sidebar    => '300 × 250',
        };
    }
}
