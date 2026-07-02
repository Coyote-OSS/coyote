<?php
namespace Modules\Campaigns;

use Libs\Arrays\arrays;
use Modules\Campaigns\Internal\CampaignBanners;
use Modules\Campaigns\Store\Campaign;
use Modules\Campaigns\Store\CampaignPayload;
use Modules\Campaigns\Store\CampaignsStore;
use Modules\Campaigns\Store\CampaignVariant;

readonly class CampaignService {
    private CampaignBannerSelector $selector;

    public function __construct(
        private ForPriviligedUsers $users,
        ForRotatingBanners         $rotate,
        private ForCurrentDate     $date,
        private CampaignsStore     $store,
    ) {
        $this->selector = new CampaignBannerSelector($rotate);
    }

    public function campaignBanners(): CampaignBanners {
        if ($this->isCampaignBannersDisabled()) {
            return $this->disabledCampaignBanners();
        }
        return $this->enabledCampaignBanners();
    }

    private function isCampaignBannersDisabled(): bool {
        return $this->users->userHasHighReputation()
            || $this->users->userIsSponsor()
            || $this->users->userIsRobot();
    }

    private function disabledCampaignBanners(): CampaignBanners {
        return new CampaignBanners([], null);
    }

    private function enabledCampaignBanners(): CampaignBanners {
        $campaigns = $this->listActiveCampaigns();
        return new CampaignBanners(
            $this->selector->campaignBanners($campaigns, VariantType::Standard, 2),
            $this->selector->campaignBanners($campaigns, VariantType::Sidebar, 1)[0] ?? null);
    }

    /**
     * @return Campaign[]
     */
    private function listActiveCampaigns(): array {
        return $this->store->listCampaigns() |> arrays::filter($this->isCampaignObjectActive(...));
    }

    public function campaignStatus(int $campaignId): string {
        return $this->campaignObjectStatus($this->store->findCampaign($campaignId));
    }

    private function isCampaignObjectActive(Campaign $campaign): bool {
        return $this->campaignObjectStatus($campaign) === 'active';
    }

    private function campaignObjectStatus(Campaign $campaign): string {
        return $this->campaignPayloadStatus(
            $campaign->payload,
            $this->campaignTotalViewCount($campaign));
    }

    private function campaignPayloadStatus(CampaignPayload $payload, int $campaignTotalViewCount): string {
        if (!$this->hasTarget($payload)) {
            return 'misconfigured';
        }
        if ($payload->targetViews !== null) {
            if ($campaignTotalViewCount >= $payload->targetViews) {
                return 'target-reached';
            }
        }
        if ($payload->activeSinceDate !== null) {
            if (!$this->date->hasStarted($payload->activeSinceDate)) {
                return 'not-started';
            }
        }
        if ($payload->activeUntilDate !== null) {
            if (!$this->date->hasNotFinished($payload->activeUntilDate)) {
                return 'finished';
            }
        }
        return 'active';
    }

    private function hasTarget(CampaignPayload $campaign): bool {
        $hasViewTarget = $campaign->targetViews !== null;
        $hasDateTarget = $campaign->activeUntilDate !== null;
        return $hasViewTarget || $hasDateTarget;
    }

    private function campaignTotalViewCount(Campaign $campaign): int {
        return \array_reduce($campaign->variants,
            fn(int $sum, CampaignVariant $variant) => $variant->views + $sum, 0);
    }
}
