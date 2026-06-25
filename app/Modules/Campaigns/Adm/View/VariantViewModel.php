<?php
namespace Coyote\Modules\Campaigns\Adm\View;

readonly class VariantViewModel {
    public function __construct(
        public string        $imageUrl,
        public CampaignStats $stats,
        public string        $bannerType,
    ) {}

    public function bannerTypeTitle(): string {
        return match ($this->bannerType) {
            'horizontal' => 'Banner',
            'sidebar'    => 'Rectangle',
            default      => throw new \Exception()
        };
    }

    public function expectedDimension(): string {
        return match ($this->bannerType) {
            'horizontal' => '728 × 90',
            'sidebar'    => '300 × 250',
            default      => throw new \Exception()
        };
    }
}
