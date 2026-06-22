<?php
namespace Coyote\Modules\Campaigns;

use Modules\Campaigns\Campaign;
use Modules\Campaigns\CampaignPayload;
use Modules\Campaigns\CampaignsStore;
use Modules\Campaigns\CampaignVariant;
use Modules\Campaigns\VariantPayload;

class EloquentCampaignsStore implements CampaignsStore {
    public function createCampaign(CampaignPayload $payload): int {
        return Eloquent\Campaign::query()
            ->create($this->campaignRow($payload))
            ->id;
    }

    private function campaignRow(CampaignPayload $payload): array {
        return [
            'campaign_key' => 'not-to-be-used-deprecated',
            'sidebar'      => 'not-to-be-used-deprecated',
            'horizontal'   => 'not-to-be-used-deprecated',
            'redirect_url' => $payload->redirectUrl,
            'active_since' => $payload->activeSinceDate,
            'active_until' => $payload->activeUntilDate,
            'target_views' => $payload->activeBelowViews,
            'name'         => $payload->name,
        ];
    }

    public function createVariant(int $campaignId, VariantPayload $payload): ?int {
        $campaign = Eloquent\Campaign::query()->find($campaignId);
        if ($campaign === null) {
            return null;
        }
        /** @var Eloquent\CampaignVariant $variant */
        $variant = $campaign->variants()->create([
            'image_url' => $payload->imageUrl,
            'type'      => $payload->bannerType,
            'views'     => 0,
            'clicks'    => 0,
        ]);
        return $variant->id;
    }

    public function updateCampaign(int $campaignId, CampaignPayload $payload): bool {
        $updated = Eloquent\Campaign::query()
            ->whereKey($campaignId)
            ->update($this->campaignRow($payload));
        return $updated === 1;
    }

    public function findCampaign(int $campaignId): ?Campaign {
        $campaign = Eloquent\Campaign::with('variants')->find($campaignId);
        if ($campaign === null) {
            return null;
        }
        return $this->parseCampaign($campaign);
    }

    public function listCampaigns(): array {
        return Eloquent\Campaign::with('variants')
            ->get()
            ->map($this->parseCampaign(...))
            ->all();
    }

    private function parseCampaign(Eloquent\Campaign $campaign): Campaign {
        return new Campaign(
            $campaign->id,
            new CampaignPayload(
                $campaign->name,
                $campaign->redirect_url,
                $campaign->active_since,
                $campaign->active_until,
                $campaign->target_views),
            $campaign->variants->map($this->parseVariant(...))->all());
    }

    private function parseVariant(Eloquent\CampaignVariant $variant): CampaignVariant {
        return new CampaignVariant(
            $variant->id,
            $variant->views,
            $variant->clicks,
            new VariantPayload($variant->type, $variant->image_url));
    }

    public function viewVariant(int $variantId): void {
        Eloquent\CampaignVariant::query()->whereKey($variantId)->increment('views');
    }

    public function clickVariant(int $variantId): void {
        Eloquent\CampaignVariant::query()->whereKey($variantId)->increment('clicks');
    }

    public function findCampaignRedirectUrl(int $variantId): ?string {
        $variant = Eloquent\CampaignVariant::query()->find($variantId);
        return $variant?->campaign->redirect_url;
    }
}
