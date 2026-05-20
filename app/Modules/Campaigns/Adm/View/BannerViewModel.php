<?php
namespace Coyote\Modules\Campaigns\Adm\View;

readonly class BannerViewModel {
    public function __construct(
        public int    $views,
        public int    $clicks,
        public float  $ctr,
        public string $bannerSrc,
    ) {}
}
