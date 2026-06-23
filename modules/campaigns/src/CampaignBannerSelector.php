<?php
namespace Modules\Campaigns;

use Modules\Campaigns\Store\Campaign;
use Modules\Campaigns\Store\CampaignVariant;

readonly class CampaignBannerSelector {
    private SlidingWindow $window;

    public function __construct(private ForRotatingBanners $rotate) {
        $this->window = new SlidingWindow();
    }

    /**
     * @param Campaign[] $campaigns
     */
    public function select(array $campaigns): CampaignBanners {
        return new CampaignBanners(
            $this->campaignBanners($campaigns, 'horizontal', 2),
            $this->campaignBanners($campaigns, 'sidebar', 1)[0]);
    }

    /**
     * @param Campaign[] $campaigns
     */
    private function campaignBanners(array $campaigns, string $bannerType, int $amount): array {
        $bannerCampaigns = $this->campaignsWithVariantsOfType($campaigns, $bannerType);
        $pickedCampaigns = $this->pick($bannerCampaigns, $amount);
        return \array_map(
            fn(Campaign $campaign) => $this->pickedBanner($campaign, $bannerType),
            $pickedCampaigns);
    }

    private function pickedBanner(Campaign $campaign, string $bannerType): CampaignBanner {
        $variants = $this->variantsOfType($campaign, $bannerType);
        /** @var CampaignVariant $variant */
        $variant = $this->pick($variants, 1)[0];
        return new CampaignBanner(
            $variant->payload->imageUrl,
            $campaign->id,
            $variant->payload->bannerType,
            $variant->id);
    }

    /**
     * @return CampaignVariant[]
     */
    private function variantsOfType(Campaign $campaign, string $bannerType): array {
        return \array_values(array_filter(
            $campaign->variants,
            fn(CampaignVariant $variant) => $variant->payload->bannerType === $bannerType));
    }

    private function pick(array $values, int $amount): array {
        return $this->window->slide($values, $amount, $this->rotate->rotationSeed());
    }

    /**
     * @param Campaign[] $campaigns
     * @return Campaign[]
     */
    private function campaignsWithVariantsOfType(array $campaigns, string $bannerType): array {
        return \array_values(\array_filter($campaigns,
            fn(Campaign $campaign): bool => $this->campaignHasVariant($campaign, $bannerType)));
    }

    public function campaignHasVariant(Campaign $campaign, string $bannerType): bool {
        return \array_any($campaign->variants,
            fn(CampaignVariant $variant) => $variant->payload->bannerType === $bannerType);
    }
}
