<?php
namespace Coyote\Modules\Campaigns\Eloquent;

use Coyote\Modules\Campaigns\Eloquent;
use Modules\Campaigns\Store\Campaign;
use Modules\Campaigns\Store\CampaignPayload;
use Modules\Campaigns\Store\CampaignsStore;
use Modules\Campaigns\Store\CampaignVariant;
use Modules\Campaigns\Store\VariantPayload;
use Modules\Campaigns\VariantType;

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
            'target_views' => $payload->targetViews,
            'name'         => $payload->name,
            'description'  => $payload->description,
            'is_premium'   => $payload->isPremium,
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
            'type'      => $this->serializeVariantType($payload),
            'views'     => 0,
            'clicks'    => 0,
            'exposures' => 0,
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
                $campaign->target_views,
                $campaign->description,
                $campaign->is_premium),
            $campaign->variants->map($this->parseVariant(...))->all());
    }

    private function parseVariant(Eloquent\CampaignVariant $variant): CampaignVariant {
        return new CampaignVariant(
            $variant->id,
            $variant->views,
            $variant->clicks,
            $variant->exposures,
            new VariantPayload($this->deserializeVariantType($variant), $variant->image_url));
    }

    public function viewVariant(int $variantId): void {
        Eloquent\CampaignVariant::query()->whereKey($variantId)->increment('views');
    }

    public function clickVariant(int $variantId): void {
        Eloquent\CampaignVariant::query()->whereKey($variantId)->increment('clicks');
    }

    public function exposeVariant(int $variantId): void {
        Eloquent\CampaignVariant::query()->whereKey($variantId)->increment('exposures');
    }

    public function findCampaignRedirectUrl(int $variantId): ?string {
        $variant = Eloquent\CampaignVariant::query()->find($variantId);
        return $variant?->campaign->redirect_url;
    }

    private function serializeVariantType(VariantPayload $payload): string {
        return match ($payload->type) {
            VariantType::Standard      => 'horizontal',
            VariantType::Sidebar       => 'sidebar',
            VariantType::LeaderBoard   => 'leaderboard',
            VariantType::StandardXl    => 'horizontal-xl',
            VariantType::SidebarXl     => 'sidebar-xl',
            VariantType::LeaderBoardXl => 'leaderboard-xl',
        };
    }

    private function deserializeVariantType(Eloquent\CampaignVariant $variant): VariantType {
        return match ($variant->type) {
            'horizontal'     => VariantType::Standard,
            'sidebar'        => VariantType::Sidebar,
            'leaderboard'    => VariantType::LeaderBoard,
            'horizontal-xl'  => VariantType::StandardXl,
            'sidebar-xl'     => VariantType::SidebarXl,
            'leaderboard-xl' => VariantType::LeaderBoardXl,
        };
    }
}
