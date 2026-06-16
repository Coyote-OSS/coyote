<?php
namespace Modules\Campaigns;

readonly class CampaignBannerSelector {
    private ProportionalInterleaver $interleaver;
    private SlidingWindow $window;

    public function __construct(private ForRotatingBanners $rotate) {
        $this->interleaver = new ProportionalInterleaver();
        $this->window = new SlidingWindow();
    }

    /**
     * @param Campaign[] $campaigns
     */
    public function select(array $campaigns): CampaignBanners {
        return $this->rotatedCampaignBanners(
            $this->bannersOfType($campaigns, 'horizontal'),
            $this->bannersOfType($campaigns, 'sidebar'));
    }

    /**
     * @param CampaignBanner[][] $horizontals
     * @param CampaignBanner[][] $sidebars
     */
    private function rotatedCampaignBanners(array $horizontals, array $sidebars): CampaignBanners {
        return new CampaignBanners(
            $this->rotatedBanners($horizontals, 2),
            $this->rotatedBanners($sidebars, 1)[0] ?? null);
    }

    /**
     * @param Campaign[] $campaigns
     * @return CampaignBanner[][]
     */
    private function bannersOfType(array $campaigns, string $bannerType): array {
        $banners = [];
        foreach ($campaigns as $campaign) {
            foreach ($campaign->bannersOfType($bannerType) as $banner) {
                $banners[$campaign->campaignKey][] = $banner;
            }
        }
        return \array_values($banners);
    }

    /**
     * @param CampaignBanner[][] $banners
     * @return CampaignBanner[]
     */
    private function rotatedBanners(array $banners, int $amount): array {
        return $this->window->slide(
            $this->interleaver->interleave($banners),
            $amount,
            $this->rotate->rotationSeed());
    }
}
