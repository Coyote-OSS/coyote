<?php
namespace Modules\Campaigns;

class Campaigns {
    private readonly CampaignsStore $store;
    private array $sidebar = [];
    private array $horizontal = [];
    private array $redirectUrls = [];

    public function __construct(
        private readonly ForPriviligedUsers $users,
        private readonly ForRotatingBanners $rotate,
    ) {
        $this->store = new InMemoryCampaignsStore();
    }

    public function add(
        string $sidebar,
        string $horizontal,
        string $campaignKey,
        string $redirectUrl,
    ): void {
        $existed = $this->store->createIfNotExists($campaignKey);
        if ($existed) {
            throw new DuplicateCampaign('Failed to add a duplicated campaign.');
        }
        $this->sidebar[$campaignKey] = new CampaignBanner($sidebar, $campaignKey);
        $this->horizontal[$campaignKey] = new CampaignBanner($horizontal, $campaignKey);
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
        return new CampaignBanners([], null);
    }

    private function enabledCampaignBanners(): CampaignBanners {
        return new CampaignBanners(
            \array_values($this->horizontal), $this->sidebar());
    }

    private function sidebar(): ?CampaignBanner {
        if (empty($this->sidebar)) {
            return null;
        }
        $rotatedCampaignKey = $this->rotate->rotateBanners($this->campaignKeys());
        return $this->sidebar[$rotatedCampaignKey];
    }

    public function redirectUrl(string $campaignKey): string {
        if ($this->campaignExists($campaignKey)) {
            return $this->redirectUrls[$campaignKey];
        }
        throw new NoSuchCampaign('Failed to get campaign redirect url.');
    }

    private function campaignKeys(): array {
        return \array_keys($this->sidebar);
    }

    private function campaignExists(string $campaignKey): bool {
        return \array_key_exists($campaignKey, $this->redirectUrls);
    }
}
