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
    public function campaignBanners(array $campaigns, DeviceType $device, VariantType $type, int $amount): array {
        $isSoleCampaign = \count($campaigns) === 1;
        $bannerCampaigns = $this->campaignsWithBannerCandidate($campaigns, $type, $device, $isSoleCampaign);
        $rotatedCampaigns = $this->rotatedCampaigns($bannerCampaigns);
        return $this->fillSlots($type, $device, $isSoleCampaign, $rotatedCampaigns, $amount);
    }

    /**
     * The feed can show several variants of the same campaign at once, unlike the other slots
     * which show at most one creative per campaign - so all eligible variants across campaigns
     * are flattened into a single pool and the render window slides across that pool directly.
     *
     * @param Campaign[] $campaigns
     * @return CampaignBanner[]
     */
    public function feedBanners(array $campaigns, VariantType $type, int $amount): array {
        $pairs = [];
        foreach ($campaigns as $campaign) {
            foreach ($campaign->variantsOfType($type) as $variant) {
                $pairs[] = [$campaign, $variant];
            }
        }
        $picked = $this->window->slide($pairs, $amount, $this->rotate->rotationSeed());
        return $picked |> arrays::map(fn(array $pair) => $this->banner($pair[0], $pair[1]));
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
    private function fillSlots(VariantType $type, DeviceType $device, bool $isSoleCampaign, array $campaigns, int $amount): array {
        $banners = [];
        $slotsAvailable = $amount;
        foreach ($campaigns as $campaign) {
            if ($slotsAvailable <= 0) {
                break;
            }
            $banner = $this->pickedBanner($campaign, $type, $device, $isSoleCampaign);
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
    private function campaignsWithBannerCandidate(array $campaigns, VariantType $type, DeviceType $device, bool $isSoleCampaign): array {
        return $campaigns |> arrays::filter(fn($campaign) => $this->hasBannerCandidate($campaign, $type, $device, $isSoleCampaign));
    }

    private function hasBannerCandidate(Campaign $campaign, VariantType $type, DeviceType $device, bool $isSoleCampaign): bool {
        if ($this->campaignHasVariant($campaign, $type)) {
            return true;
        }
        return $this->elevatedVariants($campaign, $type, $device, $isSoleCampaign) !== null;
    }

    private function campaignHasVariant(Campaign $campaign, VariantType $type): bool {
        return \array_any($campaign->variants, CampaignVariant::hasEnabledType($type));
    }

    /**
     * @param Campaign[] $campaigns
     * @return Campaign[]
     */
    private function rotatedCampaigns(array $campaigns): array {
        return $this->window->slide($campaigns, \count($campaigns), $this->rotate->rotationSeed());
    }

    private function pickedBanner(Campaign $campaign, VariantType $type, DeviceType $device, bool $isSoleCampaign): CampaignBanner {
        $variants = $this->elevatedVariants($campaign, $type, $device, $isSoleCampaign) ?? $campaign->variantsOfType($type);
        return $this->banner($campaign, $this->pick($variants, 1)[0]);
    }

    /**
     * Elevation to a more prominent variant (leaderboard, leaderboard-xl, rectangle-xl) only
     * ever happens on desktop.
     *
     * @return CampaignVariant[]|null
     */
    private function elevatedVariants(Campaign $campaign, VariantType $type, DeviceType $device, bool $isSoleCampaign): ?array {
        if ($device !== DeviceType::Desktop) {
            return null;
        }
        if ($type === VariantType::Banner) {
            if ($campaign->payload->isPremium) {
                return $campaign->variantsOfType(VariantType::LeaderBoardXl) ?: null;
            }
            if ($isSoleCampaign) {
                return $campaign->variantsOfType(VariantType::LeaderBoard) ?: null;
            }
            return null;
        }
        if ($type === VariantType::Rectangle && $campaign->payload->isPremium) {
            return $campaign->variantsOfType(VariantType::RectangleXl) ?: null;
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
