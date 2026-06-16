<?php
namespace Modules\Campaigns;

readonly class CampaignService {
    public function __construct(
        private ForPriviligedUsers $users,
        private ForRotatingBanners $rotate,
        private ForCurrentDate     $date,
        private CampaignsStore     $store,
    ) {}

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
        $activeCampaigns = $this->listActiveCampaigns();
        return $this->rotatedCampaignBanners(
            $this->bannersOfType($activeCampaigns, 'horizontal'),
            $this->bannersOfType($activeCampaigns, 'sidebar'));
    }

    /**
     * @param CampaignBanner[] $horizontals
     * @param CampaignBanner[] $sidebars
     */
    private function rotatedCampaignBanners(array $horizontals, array $sidebars): CampaignBanners {
        return new CampaignBanners(
            $this->rotatedBanners($horizontals, 2),
            $this->rotatedBanners($sidebars, 1)[0] ?? null);
    }

    /**
     * @param Campaign[] $campaigns
     */
    private function bannersOfType(array $campaigns, string $bannerType): array {
        $banners = [];
        foreach ($campaigns as $campaign) {
            foreach ($campaign->bannersOfType($bannerType) as $banner) {
                $banners[$banner->campaignKey] = $banner;
            }
        }
        return $banners;
    }

    /**
     * @return Campaign[]
     */
    private function listActiveCampaigns(): iterable {
        return \array_filter($this->store->listCampaigns(), $this->isCampaignObjectActive(...));
    }

    /**
     * @param CampaignBanner[] $banners
     */
    private function rotatedBanners(array $banners, int $amount): array {
        $keys = \array_keys($banners);
        return \array_map(
            fn($key) => $banners[$key],
            $this->rotated($keys, $amount));
    }

    private function rotated(array $keys, int $amount): array {
        return new BannerRotation()->rotatedBanners($keys, $amount,
            $this->rotate->rotationSeed());
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
