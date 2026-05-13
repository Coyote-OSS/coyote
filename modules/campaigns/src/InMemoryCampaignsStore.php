<?php
namespace Modules\Campaigns;

class InMemoryCampaignsStore implements CampaignsStore {
    /** @var string[] */
    private array $campaigns = [];

    function createIfNotExists(string $campaignKey): bool {
        $existed = \in_array($campaignKey, $this->campaigns);
        $this->campaigns[] = $campaignKey;
        return $existed;
    }
}
