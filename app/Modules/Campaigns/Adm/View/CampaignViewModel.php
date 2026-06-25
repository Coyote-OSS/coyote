<?php
namespace Coyote\Modules\Campaigns\Adm\View;

readonly class CampaignViewModel {
    /**
     * @param VariantViewModel[] $variants
     */
    public function __construct(
        public string         $name,
        public string         $redirectUrl,
        public string         $editHref,
        public string         $backHref,
        public string         $createVariantHref,
        public CampaignStats  $stats,
        public CampaignStatus $status,
        public ?string        $dateSince,
        public ?string        $dateUntil,
        public ?int           $targetViews,
        public array          $variants,
    ) {}
}
