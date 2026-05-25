<?php
namespace Coyote\Modules\Campaigns;

use Illuminate\Database;
use Illuminate\Database\Query;
use Modules\Campaigns;
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
        return $this->countCampaignEvents($campaignKey, $bannerType, 'click');
    }

    public function campaignClick(string $campaignKey, string $bannerType): void {
        $this->insertCampaignEvent($campaignKey, $bannerType, 'click');
    }

    public function campaignView(string $campaignKey, string $bannerType): void {
        $this->insertCampaignEvent($campaignKey, $bannerType, 'view');
    }

    public function campaignViewCount(string $campaignKey, string $bannerType): int {
        return $this->countCampaignEvents($campaignKey, $bannerType, 'view');
    }

    private function findCampaignId(string $campaignKey): int {
        return $this->table()
            ->where('campaign_key', $campaignKey)
            ->first('id')
            ?->id
            ?? throw new Campaigns\NoSuchCampaign('No such campaign.');
    }

    private function insertCampaignEvent(string $campaignKey, string $bannerType, string $eventType): void {
        $this->connection
            ->table('module_campaign_clicks')
            ->insert([
                'campaign_id' => $this->findCampaignId($campaignKey),
                'banner_type' => $bannerType,
                'event_type'  => $eventType,
            ]);
    }

    private function countCampaignEvents(string $campaignKey, string $bannerType, string $eventType) {
        return $this->connection
            ->table('module_campaigns')
            ->leftJoin('module_campaign_clicks', fn(Query\JoinClause $join) => $join
                ->on('campaign_id', '=', 'module_campaigns.id')
                ->where('banner_type', '=', $bannerType)
                ->where('event_type', '=', $eventType))
            ->where('campaign_key', $campaignKey)
            ->groupBy('module_campaigns.id', 'banner_type')
            ->selectRaw('COUNT(module_campaign_clicks.id) AS clicks')
            ->first('clicks')
            ?->clicks
            ?? throw new Campaigns\NoSuchCampaign('No such campaign.');
    }
}
