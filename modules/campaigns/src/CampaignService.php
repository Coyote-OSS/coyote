<?php
namespace Modules\Campaigns;

readonly class CampaignService {
    public function __construct(
        private ForPriviligedUsers $users,
        private ForRotatingBanners $rotate,
        private ForCurrentDate     $date,
        private CampaignsStore     $store,
    ) {}

    /**
     * @deprecated
     */
    public function add(
        string  $sidebar,
        string  $horizontal,
        string  $campaignKey,
        string  $redirectUrl,
        ?string $activeSince,
        ?string $activeUntil,
        ?int    $targetViews,
    ): void {
        $this->store->createIfNotExists(
            $campaignKey,
            $sidebar,
            $horizontal,
            $redirectUrl,
            $activeSince,
            $activeUntil,
            $targetViews);
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
        $sidebars = [];
        $horizontals = [];
        foreach ($this->listActiveCampaigns() as $campaign) {
            $sidebars[$campaign->campaignKey] = new CampaignBanner(
                $campaign->sidebarBanner,
                $campaign->campaignKey,
                'sidebar');
            $horizontals[$campaign->campaignKey] = new CampaignBanner(
                $campaign->horizontalBanner,
                $campaign->campaignKey,
                'horizontal');
        }
        return new CampaignBanners(
            $this->rotatedBanners($horizontals, 2),
            $this->rotatedBanners($sidebars, 1)[0] ?? null);
    }

    private function listActiveCampaigns(): iterable {
        foreach ($this->store->listCampaigns() as $campaign) {
            if ($this->isCampaignObjectActive($campaign)) {
                yield $campaign;
            }
        }
    }

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

    public function campaignStatus(string $campaignKey): string {
        return $this->campaignObjectStatus($this->store->findCampaign($campaignKey));
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
