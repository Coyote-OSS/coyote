<?php
namespace Coyote\Modules\Campaigns\Adm\View;

readonly class CampaignViewModel {
    /**
     * @param BannerViewModel[] $banners
     */
    public function __construct(
        public string         $key,
        public string         $redirectUrl,
        public string         $editHref,
        public string         $backHref,
        public CampaignStats  $stats,
        public CampaignStatus $status,
        public ?string        $dateSince,
        public ?string        $dateUntil,
        public ?int           $targetViews,
        public array          $banners,
    ) {}
}
