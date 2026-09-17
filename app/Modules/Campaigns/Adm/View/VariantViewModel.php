<?php
namespace Coyote\Modules\Campaigns\Adm\View;

use Modules\Campaigns\VariantType;

readonly class VariantViewModel {
    public function __construct(
        public string        $imageUrl,
        public CampaignStats $stats,
        public bool          $enabled,
        public string        $toggleHref,
        private VariantType  $type,
    ) {}

    public function bannerTypeTitle(): string {
        return match ($this->type) {
            VariantType::Banner        => 'Banner',
            VariantType::Rectangle     => 'Rectangle',
            VariantType::LeaderBoard   => 'LeaderBoard',
            VariantType::BannerXl      => 'Banner XL',
            VariantType::RectangleXl   => 'Rectangle XL',
            VariantType::LeaderBoardXl => 'LeaderBoard XL',
        };
    }
}
