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
    ): void {
        $existed = $this->store->createIfNotExists(
            $campaignKey,
            $sidebar,
            $horizontal,
            $redirectUrl,
            $activeSince,
            $activeUntil);
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
            if ($this->campaignActive($campaign->campaignKey)) {
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

    public function campaignActive(string $campaignKey): bool {
        [$since, $until] = $this->store->campaignActiveRange($campaignKey);
        if ($since === null || $until === null) {
            return false;
        }
        return $this->date->isRangeActive($since, $until);
    }
}
