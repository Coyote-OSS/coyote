<?php
namespace Modules\Campaigns;

class Campaigns {
    private ?string $sidebar = null;
    private array $horizontal = [];

    public function __construct(private readonly ForPriviligedUsers $users) {}

    public function add(string $sidebar, string $horizontal): void {
        $this->sidebar = $sidebar;
        $this->horizontal[] = $horizontal;
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
        return new CampaignBanners([], null);
    }

    private function enabledCampaignBanners(): CampaignBanners {
        return new CampaignBanners($this->horizontal, $this->sidebar);
    }
}
