<?php
namespace Coyote\Modules\Campaigns\Adm\View;

use Modules\Campaigns\CampaignsStore;

readonly class CampaignPresenter {
    public function __construct(private CampaignsStore $store) {}

    public function campaignStats(string $campaignKey): CampaignStats {
        $horizontal = $this->horizontalStats($campaignKey);
        $sidebar = $this->sidebarStats($campaignKey);
        return $horizontal->concat($sidebar);
    }

    public function horizontalViewModel(string $campaignKey, string $imageUrl): BannerViewModel {
        return new BannerViewModel($imageUrl, $this->horizontalStats($campaignKey));
    }

    public function sidebarViewModel(string $campaignKey, string $imageUrl): BannerViewModel {
        return new BannerViewModel($imageUrl, $this->sidebarStats($campaignKey));
    }

    private function horizontalStats(string $campaignKey): CampaignStats {
        return $this->stats($campaignKey, 'horizontal');
    }

    private function sidebarStats(string $campaignKey): CampaignStats {
        return $this->stats($campaignKey, 'sidebar');
    }

    private function stats(string $campaignKey, string $bannerType): CampaignStats {
        return new CampaignStats(
            $this->store->campaignViewCount($campaignKey, $bannerType),
            $this->store->campaignClickCount($campaignKey, $bannerType));
    }
}
