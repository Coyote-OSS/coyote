<?php
namespace Modules\Campaigns;

abstract class CampaignsStore {
    /**
     * @deprecated
     */
    public function createIfNotExists(
        string  $campaignKey,
        string  $sidebarBanner,
        string  $horizontalBanner,
        string  $redirectUrl,
        ?string $activeSince,
        ?string $activeUntil,
        ?int    $targetViews,
    ): bool {
        $createdId = $this->createCampaignReturnId(new Campaign(
            $campaignKey,
            $sidebarBanner,
            $horizontalBanner,
            $redirectUrl,
            $activeSince,
            $activeUntil,
            $targetViews));
        return $createdId === null;
    }

    public abstract function findCampaign(string $campaignKey): ?Campaign;

    /**
     * @return Campaign[]
     */
    public abstract function listCampaigns(): array;

    public abstract function campaignClick(string $campaignKey, string $bannerType): void;

    public abstract function campaignView(string $campaignKey, string $bannerType): void;

    public abstract function campaignClickCount(string $campaignKey, string $bannerType): int;

    public abstract function campaignViewCount(string $campaignKey, string $bannerType): int;

    public abstract function campaignActiveRange(string $campaignKey): array;

    public abstract function createCampaignReturnId(Campaign $campaign): ?int;

    public abstract function updateCampaign(int $campaignId, Campaign $campaign): bool;
}
