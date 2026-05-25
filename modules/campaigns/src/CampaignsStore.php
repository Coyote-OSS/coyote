<?php
namespace Modules\Campaigns;

interface CampaignsStore {
    function createIfNotExists(
        string  $campaignKey,
        string  $sidebarBanner,
        string  $horizontalBanner,
        string  $redirectUrl,
        ?string $activeSince,
        ?string $activeUntil,
    ): bool;

    /**
     * @return Campaign[]
     */
    function listCampaigns(): array;

    public function campaignClick(string $campaignKey, string $bannerType): void;

    public function campaignView(string $campaignKey, string $bannerType): void;

    public function campaignClickCount(string $campaignKey, string $bannerType): int;

    public function campaignViewCount(string $campaignKey, string $bannerType): int;
}
