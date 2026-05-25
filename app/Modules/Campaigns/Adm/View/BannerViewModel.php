<?php
namespace Coyote\Modules\Campaigns\Adm\View;

readonly class BannerViewModel {
    public function __construct(
        public string        $imageUrl,
        public CampaignStats $stats,
    ) {}
}
