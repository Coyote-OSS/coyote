<?php
namespace Coyote\Modules\Campaigns;

use Illuminate\Database;
use Illuminate\Database\Query;
use Modules\Campaigns;
use Modules\Campaigns\Campaign;
use Modules\Campaigns\CampaignsStore;
use Modules\Campaigns\CampaignVariant;

readonly class DatabaseCampaignsStore implements CampaignsStore {
    public function __construct(private Database\Connection $connection) {}

    /**
     * @return Campaign[]
     */
    public function listCampaigns(): array {
        return Eloquent\Campaign::all()->map($this->parseRow(...))->all();
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
        return $this->connection
            ->table('module_campaigns')
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

    private function parseRow(Eloquent\Campaign $campaign): Campaign {
        return Campaign::create(
            campaignKey:$campaign->campaign_key,
            sidebarBanner:'not-to-be-used-deprecated',
            horizontalBanner:'not-to-be-used-deprecated',
            redirectUrl:$campaign->redirect_url,
            activeSince:$campaign->active_since,
            activeUntil:$campaign->active_until,
            targetViews:$campaign->target_views);
    }

    public function findCampaign(int $campaignId): ?Campaign {
        $model = Eloquent\Campaign::with('variants')->find($campaignId);
        if ($model === null) {
            return null;
        }
        return new Campaign(
            $model->campaign_key,
            $model->redirect_url,
            $model->active_since,
            $model->active_until,
            $model->target_views,
            $model->variants->map($this->parseVariant(...))->all(),
        );
    }

    private function parseVariant(Eloquent\CampaignVariant $variant): CampaignVariant {
        return new CampaignVariant($variant->image_url, $variant->type);
    }

    public function createCampaignReturnId(Campaign $campaign): ?int {
        try {
            return Eloquent\Campaign::query()
                ->create([
                    'campaign_key' => $campaign->campaignKey,
                    ...$this->campaignRow($campaign),
                ])
                ->id;
        } catch (Database\UniqueConstraintViolationException) {
            return null;
        }
    }

    public function updateCampaign(int $campaignId, Campaign $campaign): bool {
        $updated = Eloquent\Campaign::query()
            ->whereKey($campaignId)
            ->update($this->campaignRow($campaign));
        return $updated === 1;
    }

    private function campaignRow(Campaign $campaign): array {
        return [
            'sidebar'      => 'not-to-be-used-deprecated',
            'horizontal'   => 'not-to-be-used-deprecated',
            'redirect_url' => $campaign->redirectUrl,
            'active_since' => $campaign->activeSince,
            'active_until' => $campaign->activeUntil,
            'target_views' => $campaign->targetViews,
        ];
    }

    public function createVariant(int $campaignId, string $imageUrl, string $type): bool {
        $campaign = Eloquent\Campaign::query()->find($campaignId);
        if ($campaign === null) {
            return false;
        }
        $campaign->variants()->create(['image_url' => $imageUrl, 'type' => $type]);
        return true;
    }
}
