<?php
namespace Coyote\Modules\Campaigns\Adm\View;

readonly class CampaignViewModel {
    public function __construct(
        public string $key,
        public string $redirectUrl,
        public string $editHref,
        public int    $views,
        public int    $clicks,
    ) {}
}
