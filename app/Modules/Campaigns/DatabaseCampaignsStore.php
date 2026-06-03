<?php
namespace Coyote\Modules\Campaigns;

use Illuminate\Database;
use Illuminate\Database\Query;
use Modules\Campaigns;
use Modules\Campaigns\Campaign;
use Modules\Campaigns\CampaignsStore;

readonly class DatabaseCampaignsStore implements CampaignsStore {
    public function __construct(private Database\Connection $connection) {}

    /**
     * @return Eloquent\Campaign[]
     */
    public function listCampaigns(): array {
        return $this->table()->get()->map($this->parseRow(...))->all();
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

    private function insertCampaignEvent(string $campaignKey, string $bannerType, string $eventType): void {
        $this->connection
            ->table('module_campaign_clicks')
            ->insert([
                'campaign_id' => $this->findCampaignId($campaignKey),
                'banner_type' => $bannerType,
                'event_type'  => $eventType,
            ]);
    }

    private function findCampaignId(string $campaignKey): int {
        return $this->table()
            ->where('campaign_key', $campaignKey)
            ->first('id')
            ?->id
            ?? throw new Campaigns\NoSuchCampaign('No such campaign.');
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

    public function campaignActiveRange(string $campaignKey): array {
        $row = $this->table()
            ->where('campaign_key', $campaignKey)
            ->first(['active_since', 'active_until']);
        if ($row === null) {
            throw new Campaigns\NoSuchCampaign('No such campaign.');
        }
        return [$row->active_since, $row->active_until];
    }

    public function findCampaign(string $campaignKey): ?Campaign {
        $campaign = $this->table()->where('campaign_key', $campaignKey)->first();
        if ($campaign === null) {
            return null;
        }
        return $this->parseRow($campaign);
    }

    private function parseRow(object $campaign): Campaign {
        return Campaign::create(
            campaignKey:$campaign->campaign_key,
            sidebarBanner:$campaign->sidebar,
            horizontalBanner:$campaign->horizontal,
            redirectUrl:$campaign->redirect_url,
            activeSince:$campaign->active_since,
            activeUntil:$campaign->active_until,
            targetViews:$campaign->target_views);
    }

    public function createCampaignReturnId(Campaign $campaign): ?int {
        try {
            return $this->table()->insertGetId([
                'campaign_key' => $campaign->campaignKey,
                ...$this->campaignRow($campaign),
            ]);
        } catch (Database\UniqueConstraintViolationException) {
            return null;
        }
    }

    public function updateCampaign(int $campaignId, Campaign $campaign): bool {
        $updated = $this->table()
            ->where('id', $campaignId)
            ->update($this->campaignRow($campaign));
        return $updated === 1;
    }

    private function campaignRow(Campaign $campaign): array {
        return [
            'sidebar'      => $campaign->sidebarBanner(),
            'horizontal'   => $campaign->horizontalBanner(),
            'redirect_url' => $campaign->redirectUrl,
            'active_since' => $campaign->activeSince,
            'active_until' => $campaign->activeUntil,
            'target_views' => $campaign->targetViews,
        ];
    }
}
