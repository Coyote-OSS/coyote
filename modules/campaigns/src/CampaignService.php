<?php
namespace Modules\Campaigns;

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
            || $this->users->userIsSponsor();
    }

    private function disabledCampaignBanners(): CampaignBanners {
        return new CampaignBanners([], null);
    }

    private function enabledCampaignBanners(): CampaignBanners {
        return $this->selector->select($this->listActiveCampaigns());
    }

    /**
     * @return Campaign[]
     */
    private function listActiveCampaigns(): array {
        return \array_filter($this->store->listCampaigns(), $this->isCampaignObjectActive(...));
    }

    /**
     * @deprecated
     */
    public function redirectUrl(int $campaignId): string {
        $redirectUrls = [];
        foreach ($this->store->listCampaigns() as $campaign) {
            $redirectUrls[$campaign->id] = $campaign->payload->redirectUrl;
        }
        if (\array_key_exists($campaignId, $redirectUrls)) {
            return $redirectUrls[$campaignId];
        }
        throw new NoSuchCampaign('Failed to get campaign redirect url.');
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
        if ($payload->activeBelowViews !== null) {
            if ($payload->activeBelowViews < $campaignTotalViewCount) {
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
        $hasViewTarget = $campaign->activeBelowViews !== null;
        $hasDateTarget = $campaign->activeUntilDate !== null;
        return $hasViewTarget || $hasDateTarget;
    }

    private function campaignTotalViewCount(Campaign $campaign): int {
        return \array_reduce($campaign->variants,
            fn(int $sum, CampaignVariant $variant) => $variant->views + $sum, 0);
    }
}
