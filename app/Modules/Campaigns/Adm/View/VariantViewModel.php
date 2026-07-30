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
            VariantType::Standard      => 'Banner',
            VariantType::Sidebar       => 'Rectangle',
            VariantType::LeaderBoard   => 'LeaderBoard',
            VariantType::StandardXl    => 'Banner XL',
            VariantType::SidebarXl     => 'Rectangle XL',
            VariantType::LeaderBoardXl => 'LeaderBoard XL',
        };
    }
}
