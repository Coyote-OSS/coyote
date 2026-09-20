<?php
namespace Features\Dsl\Driver\Channel\AcceptanceChannel;

class CampaignIdMapping {
    /** @var array<string, int> */
    private array $ids = [];

    public function setCampaignId(string $campaign, int $campaignId): void {
        if (\array_key_exists($campaign, $this->ids)) {
            throw new \Exception("Campaign already has an id assigned: $campaign");
        }
        $this->ids[$campaign] = $campaignId;
    }

    public function getCampaignId(string $campaign): int {
        return $this->ids[$campaign] ?? throw new \Exception(
            "Campaign has not been assigned an id: $campaign",
        );
    }
}
