<?php
namespace Modules\Campaigns;

class InMemoryCampaignsStore implements CampaignsStore {
    /** @var string[] */
    private array $campaigns = [];

    public function createIfNotExists(
        string $campaignKey,
        string $sidebarBanner,
        string $horizontalBanner,
        string $redirectUrl,
    ): bool {
        $existed = \array_key_exists($campaignKey, $this->campaigns);
        $this->campaigns[$campaignKey] = new Campaign(
            $campaignKey,
            $sidebarBanner,
            $horizontalBanner,
            $redirectUrl);
        return $existed;
    }

    /**
     * @return Campaign[]
     */
    public function listCampaigns(): array {
        return \array_values($this->campaigns);
    }
}
