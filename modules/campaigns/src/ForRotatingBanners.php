<?php
namespace Modules\Campaigns;

interface ForRotatingBanners {
    /**
     * @param string[] $banners
     */
    public function rotateBanners(array $banners): string;
}
