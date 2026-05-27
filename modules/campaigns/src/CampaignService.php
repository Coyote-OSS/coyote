<?php
namespace Modules\Campaigns;

readonly class CampaignService {
    public function __construct(
        private ForPriviligedUsers $users,
        private ForRotatingBanners $rotate,
        private ForCurrentDate     $date,
        private CampaignsStore     $store,
    ) {}

    public function add(
        string  $sidebar,
        string  $horizontal,
        string  $campaignKey,
        string  $redirectUrl,
        ?string $activeSince,
        ?string $activeUntil,
        ?int    $targetViews,
    ): void {
        $existed = $this->store->createIfNotExists(
            $campaignKey,
            $sidebar,
            $horizontal,
            $redirectUrl,
            $activeSince,
            $activeUntil,
            $targetViews);
        if ($existed) {
            throw new DuplicateCampaign('Failed to add a duplicated campaign.');
        }
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
            $horizontals[] = new CampaignBanner(
                $campaign->horizontalBanner,
                $campaign->campaignKey,
                'horizontal');
        }
        return new CampaignBanners($horizontals, $this->rotatedSidebarBanner($sidebars));
    }

    private function listActiveCampaigns(): iterable {
        foreach ($this->store->listCampaigns() as $campaign) {
            if ($this->isCampaignObjectActive($campaign)) {
                yield $campaign;
            }
        }
    }

    /**
     * @param CampaignBanner[] $sidebarBanners
     */
    private function rotatedSidebarBanner(array $sidebarBanners): ?CampaignBanner {
        if (empty($sidebarBanners)) {
            return null;
        }
        return $sidebarBanners[$this->rotate->rotateBanners(\array_keys($sidebarBanners))];
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

    public function isCampaignActive(string $campaignKey): bool {
        return $this->isCampaignObjectActive($this->store->findCampaign($campaignKey));
    }

    private function isCampaignObjectActive(Campaign $campaign): bool {
        if (!$this->hasTarget($campaign)) {
            return false;
        }
        if ($campaign->targetViews !== null) {
            if ($campaign->targetViews < $this->campaignTotalViewCount($campaign)) {
                return false;
            }
        }
        if ($campaign->activeSince !== null) {
            if (!$this->date->hasStarted($campaign->activeSince)) {
                return false;
            }
        }
        if ($campaign->activeUntil !== null) {
            if (!$this->date->hasNotFinished($campaign->activeUntil)) {
                return false;
            }
        }
        return true;
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
