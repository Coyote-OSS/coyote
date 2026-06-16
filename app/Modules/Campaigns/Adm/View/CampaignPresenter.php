<?php
namespace Coyote\Modules\Campaigns\Adm\View;

use Modules\Campaigns\CampaignService;
use Modules\Campaigns\CampaignsStore;
use Modules\Campaigns\CampaignVariant;

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
    public function bannerViewModelsById(int $campaignId): array {
        $campaign = $this->store->findCampaign($campaignId);
//        $this->horizontalStats($campaignKey);
//        $this->sidebarStats($campaignKey);
        return \array_map($this->bannerViewModel(...), $campaign->variants);
    }

    private function bannerViewModel(CampaignVariant $variant): BannerViewModel {
        return new BannerViewModel(
            $variant->bannerUrl,
            new CampaignStats(-1, -1),
            $variant->bannerType);
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

    public function campaignStatus(int $campaignId): CampaignStatus {
        return new CampaignStatus($this->campaigns->campaignStatus($campaignId));
    }
}
