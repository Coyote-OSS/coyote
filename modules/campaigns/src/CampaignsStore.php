<?php
namespace Modules\Campaigns;

interface CampaignsStore {
    public function createCampaign(CampaignPayload $payload): int;

    public function createVariant(int $campaignId, VariantPayload $payload): ?int;

    public function updateCampaign(int $campaignId, CampaignPayload $payload): bool;

    public function findCampaign(int $campaignId): ?Campaign;

    /**
     * @return Campaign[]
     */
    public function listCampaigns(): array;

    public function viewVariant(int $variantId): void;

    public function clickVariant(int $variantId): void;

    public function findCampaignRedirectUrl(int $variantId): ?string;
}
