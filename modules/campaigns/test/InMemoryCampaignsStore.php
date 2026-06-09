<?php
namespace Test\Modules\Campaigns;

use Modules\Campaigns\Campaign;
use Modules\Campaigns\CampaignsStore;

class InMemoryCampaignsStore implements CampaignsStore {
    private array $views = ['horizontal' => 0, 'sidebar' => 0];
    private array $clicks = ['horizontal' => 0, 'sidebar' => 0];
    /** @var Campaign[] */
    private array $campaigns = [];

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

    public function campaignActiveRange(string $campaignKey): array {
        $campaign = $this->findCampaign($campaignKey) ?? throw new \Exception();
        return [
            $campaign->activeSince,
            $campaign->activeUntil,
        ];
    }

    public function findCampaign(string $campaignKey): ?Campaign {
        return $this->campaigns[$campaignKey] ?? null;
    }

    public function createCampaignReturnId(Campaign $campaign): ?int {
        $existed = \array_key_exists($campaign->campaignKey, $this->campaigns);
        $this->campaigns[$campaign->campaignKey] = $campaign;
        return $existed ? null : 1;
    }

    public function updateCampaign(int $campaignId, Campaign $campaign): bool {
        throw new \Exception();
    }

    public function createVariant(int $campaignId, string $imageUrl, string $type): bool {
        throw new \Exception('Not implemented');
    }

    public function findCampaignById(int $campaignId): ?Campaign {
        throw new \Exception('Not implemented');
    }
}
