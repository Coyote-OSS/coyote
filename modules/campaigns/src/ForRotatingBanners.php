<?php
namespace Modules\Campaigns;

interface ForRotatingBanners {
    /**
     * @param string[] $campaignKeys
     */
    public function rotateBanners(array $campaignKeys): string;
}
