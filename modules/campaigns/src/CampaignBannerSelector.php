<?php
namespace Modules\Campaigns;

use Libs\Arrays\arrays;
use Modules\Campaigns\Internal\CampaignBanner;
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
        $rotatedCampaigns = $this->rotatedCampaigns($bannerCampaigns);
        $isSoleCampaign = \count($bannerCampaigns) === 1;
        return $this->fillSlots($type, $rotatedCampaigns, $amount, $isSoleCampaign);
    }

    /**
     * A leaderboard banner spans the full row, so it can only be shown alone: campaigns are
     * walked in rotation order and skipped whenever they no longer fit the remaining slots,
     * which also means a leaderboard campaign doesn't permanently starve out the others -
     * whichever candidate is up first in a given rotation gets the slots it needs.
     *
     * @param Campaign[] $campaigns
     * @return CampaignBanner[]
     */
    private function fillSlots(VariantType $type, array $campaigns, int $amount, bool $isSoleCampaign): array {
        $banners = [];
        $slotsAvailable = $amount;
        foreach ($campaigns as $campaign) {
            if ($slotsAvailable <= 0) {
                break;
            }
            $banner = $this->pickedBanner($campaign, $type, $isSoleCampaign);
            $width = $this->isLeaderBoardType($banner->type) ? $amount : 1;
            if ($width > $slotsAvailable) {
                continue;
            }
            $banners[] = $banner;
            $slotsAvailable -= $width;
        }
        return $banners;
    }

    private function isLeaderBoardType(VariantType $type): bool {
        return $type === VariantType::LeaderBoard || $type === VariantType::LeaderBoardXl;
    }

    /**
     * @param Campaign[] $campaigns
     * @return Campaign[]
     */
    private function campaignsWithVariantsOfType(array $campaigns, VariantType $type): array {
        return $campaigns |> arrays::filter(fn($campaign) => $this->campaignHasVariant($campaign, $type));
    }

    private function campaignHasVariant(Campaign $campaign, VariantType $type): bool {
        return \array_any($campaign->variants, CampaignVariant::hasType($type));
    }

    /**
     * @param Campaign[] $campaigns
     * @return Campaign[]
     */
    private function rotatedCampaigns(array $campaigns): array {
        return $this->window->slide($campaigns, \count($campaigns), $this->rotate->rotationSeed());
    }

    private function pickedBanner(Campaign $campaign, VariantType $type, bool $isSoleCampaign): CampaignBanner {
        $variants = $this->elevatedHorizontalVariants($campaign, $type, $isSoleCampaign) ?? $campaign->variantsOfType($type);
        return $this->banner($campaign, $this->pick($variants, 1)[0]);
    }

    /**
     * @return CampaignVariant[]|null
     */
    private function elevatedHorizontalVariants(Campaign $campaign, VariantType $type, bool $isSoleCampaign): ?array {
        if ($type !== VariantType::Standard) {
            return null;
        }
        if ($campaign->payload->isPremium) {
            $variants = $campaign->variantsOfType(VariantType::LeaderBoardXl);
            return $variants ?: null;
        } else if ($isSoleCampaign) {
            $variants = $campaign->variantsOfType(VariantType::LeaderBoard);
            return $variants ?: null;
        }
        return null;
    }

    private function banner(Campaign $campaign, CampaignVariant $variant): CampaignBanner {
        return new CampaignBanner(
            $variant->payload->imageUrl,
            $campaign->id,
            $variant->payload->type,
            $variant->id);
    }

    private function pick(array $values, int $amount): array {
        return $this->window->slide($values, $amount, $this->rotate->rotationSeed());
    }
}
