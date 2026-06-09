<?php
namespace Modules\Campaigns;

interface CampaignsStore {
    /**
     * @deprecated
     */
    public function findCampaign(string $campaignKey): ?Campaign;

    /**
     * @return Campaign[]
     */
    public function listCampaigns(): array;

    public function campaignClick(string $campaignKey, string $bannerType): void;

    public function campaignView(string $campaignKey, string $bannerType): void;

    public function campaignClickCount(string $campaignKey, string $bannerType): int;

    public function campaignViewCount(string $campaignKey, string $bannerType): int;

    public function campaignActiveRange(string $campaignKey): array;

    public function createCampaignReturnId(Campaign $campaign): ?int;

    public function updateCampaign(int $campaignId, Campaign $campaign): bool;

    public function createVariant(int $campaignId, string $imageUrl, string $type): bool;

    public function findCampaignById(int $campaignId): ?Campaign;
}
