<?php
namespace Coyote\Modules\Campaigns;

use Illuminate\Database\Connection;
use Illuminate\Database\Query;
use Modules\Campaigns\CampaignsStore;

readonly class DatabaseCampaignsStore implements CampaignsStore {
    public function __construct(private Connection $connection) {}

    public function createIfNotExists(string $campaignKey): bool {
        return $this->insertAndReturnInserted($campaignKey) === 0;
    }

    private function insertAndReturnInserted(string $campaignKey): int {
        return $this->table()->insertOrIgnore([
            'key' => $campaignKey,
        ]);
    }

    private function table(): Query\Builder {
        return $this->connection->table('campaign_keys');
    }
}
