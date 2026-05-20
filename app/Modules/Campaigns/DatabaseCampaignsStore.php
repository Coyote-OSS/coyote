<?php
namespace Coyote\Modules\Campaigns;

use Illuminate\Database;
use Illuminate\Database\Query;
use Illuminate\Support\Facades\DB;
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

    public function campaignClickCount(string $campaignKey, string $bannerType): int {
        return $this->connection
            ->table('module_campaigns')
            ->leftJoin('module_campaign_clicks', function (Query\JoinClause $join) use ($bannerType): void {
                $join->on('campaign_id', '=', 'module_campaigns.id');
                $join->on('banner_type', '=', DB::raw("'$bannerType'"));
            })
            ->where('campaign_key', $campaignKey)
            ->groupBy('module_campaigns.id', 'banner_type')
            ->selectRaw('COUNT(module_campaign_clicks.id) AS clicks')
            ->first('clicks')
            ?->clicks
            ?? throw new \Exception('No such campaign.');
    }

    public function campaignClick(string $campaignKey, string $bannerType): void {
        $this->connection
            ->table('module_campaign_clicks')
            ->insert([
                'campaign_id' => $this->findCampaignId($campaignKey),
                'banner_type' => $bannerType,
            ]);
    }

    private function findCampaignId(string $campaignKey): int {
        return $this->table()
            ->where('campaign_key', $campaignKey)
            ->first('id')
            ->id;
    }
}
