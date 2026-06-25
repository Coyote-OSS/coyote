<?php
namespace Coyote\Modules\Campaigns\Adm\View;

use Modules\Campaigns\VariantType;

readonly class VariantViewModel {
    public function __construct(
        public string        $imageUrl,
        public CampaignStats $stats,
        private VariantType  $type,
    ) {}

    public function bannerTypeTitle(): string {
        return match ($this->type) {
            VariantType::Standard    => 'Banner',
            VariantType::Sidebar     => 'Rectangle',
            VariantType::LeaderBoard => 'LeaderBoard',
        };
    }

    public function expectedDimension(): string {
        return match ($this->type) {
            VariantType::Standard    => '728 × 90',
            VariantType::Sidebar     => '300 × 250',
            VariantType::LeaderBoard => '1140 × 90',
        };
    }
}
