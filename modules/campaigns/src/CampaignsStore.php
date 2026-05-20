<?php
namespace Modules\Campaigns;

interface CampaignsStore {
    function createIfNotExists(
        string $campaignKey,
        string $sidebarBanner,
        string $horizontalBanner,
        string $redirectUrl,
    ): bool;

    /**
     * @return Campaign[]
     */
    function listCampaigns(): array;

    public function campaignClick(string $campaignKey, string $bannerType): void;

    public function campaignClickCount(string $campaignKey, string $bannerType): int;
}
