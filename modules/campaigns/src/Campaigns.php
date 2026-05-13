<?php
namespace Modules\Campaigns;

class Campaigns {
    private ?string $sidebar = null;
    private ?string $horizontal = null;

    public function __construct(private readonly ForPriviligedUsers $users) {}

    public function campaignBanners(): CampaignBanners {
        if ($this->users->userHasHighReputation()
            || $this->users->userIsSponsor()) {
            return new CampaignBanners([], null);
        }
        if ($this->horizontal === null) {
            return new CampaignBanners([], $this->sidebar);
        }
        return new CampaignBanners([$this->horizontal], $this->sidebar);
    }

    public function add(string $sidebar, string $horizontal): void {
        $this->sidebar = $sidebar;
        $this->horizontal = $horizontal;
    }
}
