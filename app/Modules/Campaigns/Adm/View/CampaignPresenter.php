<?php
namespace Coyote\Modules\Campaigns\Adm\View;

use Modules\Campaigns\CampaignService;
use Modules\Campaigns\CampaignsStore;

readonly class CampaignPresenter {
    public function __construct(
        private CampaignsStore  $store,
        private CampaignService $campaigns,
    ) {}

    public function campaignStats(string $campaignKey): CampaignStats {
        $horizontal = $this->horizontalStats($campaignKey);
        $sidebar = $this->sidebarStats($campaignKey);
        return $horizontal->concat($sidebar);
    }

    /**
     * @return BannerViewModel[]
     */
    public function bannerViewModels(string $campaignKey): array {
        $campaign = $this->store->findCampaign($campaignKey);
        return [
            new BannerViewModel($campaign->horizontalBanner(), new CampaignStats(0, 0), 'horizontal'),
            new BannerViewModel($campaign->sidebarBanner(), new CampaignStats(0, 0), 'sidebar'),
        ];
    }

    public function horizontalViewModel(string $campaignKey, string $imageUrl): BannerViewModel {
        return new BannerViewModel(
            $imageUrl,
            $this->horizontalStats($campaignKey),
            'horizontal');
    }

    public function sidebarViewModel(string $campaignKey, string $imageUrl): BannerViewModel {
        return new BannerViewModel(
            $imageUrl,
            $this->sidebarStats($campaignKey),
            'sidebar');
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

    public function campaignStatus(string $campaignKey): CampaignStatus {
        return new CampaignStatus($this->campaigns->campaignStatus($campaignKey));
    }
}
