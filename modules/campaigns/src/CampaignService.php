<?php
namespace Modules\Campaigns;

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

    public function redirectUrl(string $campaignKey): string {
        $redirectUrls = [];
        foreach ($this->store->listCampaigns() as $campaign) {
            $redirectUrls[$campaign->campaignKey] = $campaign->redirectUrl;
        }
        if (\array_key_exists($campaignKey, $redirectUrls)) {
            return $redirectUrls[$campaignKey];
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
        if (!$this->hasTarget($campaign)) {
            return 'misconfigured';
        }
        if ($campaign->targetViews !== null) {
            if ($campaign->targetViews < $this->campaignTotalViewCount($campaign)) {
                return 'target-reached';
            }
        }
        if ($campaign->activeSince !== null) {
            if (!$this->date->hasStarted($campaign->activeSince)) {
                return 'not-started';
            }
        }
        if ($campaign->activeUntil !== null) {
            if (!$this->date->hasNotFinished($campaign->activeUntil)) {
                return 'finished';
            }
        }
        return 'active';
    }

    private function campaignTotalViewCount(Campaign $campaign): int {
        return $this->store->campaignViewCount($campaign->campaignKey, 'horizontal')
            + $this->store->campaignViewCount($campaign->campaignKey, 'sidebar');
    }

    private function hasTarget(Campaign $campaign): bool {
        $hasViewTarget = $campaign->targetViews !== null;
        $hasDateTarget = $campaign->activeUntil !== null;
        return $hasViewTarget || $hasDateTarget;
    }
}
