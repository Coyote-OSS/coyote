<?php
namespace Test\Modules\Campaigns;

use Modules\Campaigns\Campaign;
use Modules\Campaigns\CampaignsStore;

class InMemoryCampaignsStore implements CampaignsStore {
    private array $views = ['horizontal' => 0, 'sidebar' => 0];
    private array $clicks = ['horizontal' => 0, 'sidebar' => 0];
    /** @var Campaign[] */
    private array $campaigns = [];
    /** @var Campaign[] */
    private array $campaignsById = [];

    /**
     * @return Campaign[]
     */
    public function listCampaigns(): array {
        return \array_values($this->campaigns);
    }

    public function campaignViewCount(string $campaignKey, string $bannerType): int {
        return $this->views[$bannerType] ?? throw new \Exception();
    }

    public function campaignClickCount(string $campaignKey, string $bannerType): int {
        return $this->clicks[$bannerType] ?? throw new \Exception();
    }

    public function campaignView(string $campaignKey, string $bannerType): void {
        throw new \Exception();
    }

    public function campaignClick(string $campaignKey, string $bannerType): void {
        throw new \Exception();
    }

    public function stubCampaignViews(int $views, string $bannerType): void {
        if (!array_key_exists($bannerType, $this->views)) {
            throw new \Exception();
        }
        $this->views[$bannerType] = $views;
    }

    public function stubCampaignClicks(int $clicks, string $bannerType): void {
        if (!array_key_exists($bannerType, $this->clicks)) {
            throw new \Exception();
        }
        $this->clicks[$bannerType] = $clicks;
    }

    public function findCampaign(int $campaignId): ?Campaign {
        return $this->campaignsById[$campaignId] ?? null;
    }

    public function createCampaignReturnId(Campaign $campaign): ?int {
        $newId = \count($this->campaignsById) + 1;
        $existed = \array_key_exists($campaign->campaignKey, $this->campaigns);
        $this->campaigns[$campaign->campaignKey] = $campaign;
        $this->campaignsById[$newId] = $campaign;
        return $existed ? null : $newId;
    }

    public function updateCampaign(int $campaignId, Campaign $campaign): bool {
        throw new \Exception();
    }

    public function createVariant(int $campaignId, string $imageUrl, string $type): bool {
        throw new \Exception('Not implemented');
    }
}
