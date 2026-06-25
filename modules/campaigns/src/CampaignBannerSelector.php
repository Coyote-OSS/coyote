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
     * @return CampaignBanner[]
     */
    public function campaignBanners(array $campaigns, VariantType $type, int $amount): array {
        $bannerCampaigns = $this->campaignsWithVariantsOfType($campaigns, $type);
        $pickedCampaigns = $this->pick($bannerCampaigns, $amount);
        return $this->pickedCampaignsPickedBanners($type, $pickedCampaigns);
    }

    /**
     * @param Campaign[] $campaigns
     * @return Campaign[]
     */
    private function campaignsWithVariantsOfType(array $campaigns, VariantType $type): array {
        return \array_values(\array_filter($campaigns,
            fn(Campaign $campaign): bool => $this->campaignHasVariant($campaign, $type)));
    }

    private function campaignHasVariant(Campaign $campaign, VariantType $type): bool {
        return \array_any($campaign->variants,
            fn(CampaignVariant $variant) => $variant->payload->type === $type);
    }

    private function pickedCampaignsPickedBanners(VariantType $type, array $pickedCampaigns): array {
        return \array_map(
            fn(Campaign $campaign) => $this->pickedBanner($campaign, $type),
            $pickedCampaigns);
    }

    private function pickedBanner(Campaign $campaign, VariantType $type): CampaignBanner {
        $variants = $this->variantsOfType($campaign, $type);
        return $this->banner($campaign, $this->pick($variants, 1)[0]);
    }

    private function banner(Campaign $campaign, CampaignVariant $variant): CampaignBanner {
        return new CampaignBanner(
            $variant->payload->imageUrl,
            $campaign->id,
            $variant->payload->type,
            $variant->id);
    }

    /**
     * @return CampaignVariant[]
     */
    private function variantsOfType(Campaign $campaign, VariantType $type): array {
        return \array_values(array_filter(
            $campaign->variants,
            fn(CampaignVariant $variant) => $variant->payload->type === $type));
    }

    private function pick(array $values, int $amount): array {
        return $this->window->slide($values, $amount, $this->rotate->rotationSeed());
    }
}
