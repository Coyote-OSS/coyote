<?php
namespace Modules\Campaigns;

class InMemoryCampaignsStore implements CampaignsStore {
    private array $views = ['horizontal' => 0, 'sidebar' => 0];
    private array $clicks = ['horizontal' => 0, 'sidebar' => 0];
    /** @var (string|null)[][] */
    private array $activeRanges = [];
    /** @var string[] */
    private array $campaigns = [];

    public function createIfNotExists(
        string  $campaignKey,
        string  $sidebarBanner,
        string  $horizontalBanner,
        string  $redirectUrl,
        ?string $activeSince,
        ?string $activeUntil,
    ): bool {
        $existed = \array_key_exists($campaignKey, $this->campaigns);
        $this->campaigns[$campaignKey] = new Campaign(
            $campaignKey,
            $sidebarBanner,
            $horizontalBanner,
            $redirectUrl,
            '',
            '');
        return $existed;
    }

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

    public function campaignView(string $campaignKey, string $bannerType): void {}

    public function campaignClick(string $campaignKey, string $bannerType): void {}

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
        return $this->activeRanges[$campaignKey] ?? throw new \Exception();
    }

    public function stubCampaignActiveRange(string $campaignKey, ?string $since, ?string $until): void {
        $this->activeRanges[$campaignKey] = [$since, $until];
    }
}
