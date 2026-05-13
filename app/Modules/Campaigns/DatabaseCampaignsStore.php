<?php
namespace Coyote\Modules\Campaigns;

use Illuminate\Database;
use Illuminate\Database\Query;
use Modules\Campaigns\Campaign;
use Modules\Campaigns\CampaignsStore;

readonly class DatabaseCampaignsStore implements CampaignsStore {
    public function __construct(private Database\Connection $connection) {}

    public function createIfNotExists(
        string $campaignKey,
        string $sidebarBanner,
        string $horizontalBanner,
        string $redirectUrl,
    ): bool {
        $inserted = $this->table()->insertOrIgnore([
            'campaign_key' => $campaignKey,
            'sidebar'      => $sidebarBanner,
            'horizontal'   => $horizontalBanner,
            'redirect_url' => $redirectUrl,
        ]);
        return $inserted === 0;
    }

    /**
     * @return Eloquent\Campaign[]
     */
    public function listCampaigns(): array {
        return $this->table()
            ->get()
            ->map(fn(object $row) => new Campaign(
                campaignKey:$row->campaign_key,
                sidebarBanner:$row->sidebar,
                horizontalBanner:$row->horizontal,
                redirectUrl:$row->redirect_url,
            ))
            ->all();
    }

    private function table(): Query\Builder {
        return $this->connection->table('module_campaigns');
    }
}
