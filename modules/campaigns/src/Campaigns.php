<?php
namespace Modules\Campaigns;

class Campaigns {
    private array $sidebar = [];
    private array $horizontal = [];
    private array $redirectUrls = [];

    public function __construct(
        private readonly ForPriviligedUsers $users,
        private readonly ForRotatingBanners $rotate,
    ) {}

    public function add(
        string $sidebar,
        string $horizontal,
        string $campaignKey,
        string $redirectUrl,
    ): void {
        if (\array_key_exists($campaignKey, $this->redirectUrls)) {
            throw new DuplicateCampaign('Failed to add a duplicated campaign.');
        }
        $this->sidebar[] = $sidebar;
        $this->horizontal[] = $horizontal;
        $this->redirectUrls[$campaignKey] = $redirectUrl;
    }

    public function campaignBanners(): CampaignBanners {
        if ($this->campaignBannersDisabled()) {
            return $this->disabledCampaignBanners();
        }
        return $this->enabledCampaignBanners();
    }

    private function campaignBannersDisabled(): bool {
        return $this->users->userHasHighReputation()
            || $this->users->userIsSponsor();
    }

    private function disabledCampaignBanners(): CampaignBanners {
        return new CampaignBanners(
            [], null, null);
    }

    private function enabledCampaignBanners(): CampaignBanners {
        return new CampaignBanners(
            $this->horizontal,
            $this->sidebar(),
            $this->sidebarCampaignKey());
    }

    private function sidebar(): ?string {
        if (empty($this->sidebar)) {
            return null;
        }
        return $this->rotate->rotateBanners($this->sidebar);
    }

    private function sidebarCampaignKey(): ?string {
        if (empty($this->redirectUrls)) {
            return null;
        }
        return $this->rotate->rotateBanners(\array_keys($this->redirectUrls));
    }

    public function redirectUrl(string $campaignKey): string {
        if (\array_key_exists($campaignKey, $this->redirectUrls)) {
            return $this->redirectUrls[$campaignKey];
        }
        throw new NoSuchCampaign('Failed to get campaign redirect url.');
    }
}
