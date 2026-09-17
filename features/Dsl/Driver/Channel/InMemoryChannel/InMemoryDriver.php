<?php
namespace Features\Dsl\Driver\Channel\InMemoryChannel;

use Features\Dsl\Driver\Driver;
use Modules\Campaigns\Store\Campaign;
use Modules\Campaigns\Store\CampaignPayload;
use Modules\Campaigns\Store\CampaignsStore;
use Modules\Campaigns\Store\VariantPayload;
use Modules\Campaigns\VariantType;

class InMemoryDriver implements Driver {
    private array $campaignIds = [];
    private string $deviceType = '';
    private int $rotationSeed = 0;

    public function __construct(private readonly CampaignsStore $store) {}

    public function createCampaign(string $campaign, bool $premium): void {
        $this->campaignIds[$campaign] = $this->store->createCampaign(new CampaignPayload(
            $campaign, '', null, null, null, null, $premium, null,
        ));
    }

    public function addVariant(string $campaign, string $variantType, string $variantUrl): void {
        $this->store->createVariant($this->campaignIds[$campaign], new VariantPayload(
            $this->parseVariantType($variantType),
            $variantUrl,
        ));
    }

    public function resolveVariantsForUser(string $deviceType): void {
        $this->deviceType = $deviceType;
        $this->rotationSeed++;
    }

    public function variantsForSlot(string $slotType): array {
        $variants = $this->allVariantsForSlot($slotType);
        $result = [];
        for ($i = 0; $i < min($this->slotWindowSize($slotType), count($variants)); $i++) {
            $result[] = $variants[($this->rotationSeed - 1 + $i) % count($variants)];
        }
        return $result;
    }

    private function slotWindowSize(string $slotType): int {
        if ($slotType === 'feed') {
            return 2;
        }
        return 1;
    }

    /**
     * @return string[]
     */
    private function allVariantsForSlot(string $slotType): array {
        $campaigns = $this->store->listCampaigns();
        $type = $this->resolvedVariantType($slotType, $campaigns);
        $urls = [];
        foreach ($campaigns as $campaign) {
            foreach ($campaign->variantsOfType($type) as $variant) {
                $urls[] = $variant->payload->imageUrl;
            }
        }
        return $urls;
    }

    /**
     * @param Campaign[] $campaigns
     */
    private function resolvedVariantType(string $slotType, array $campaigns): VariantType {
        if ($slotType === 'square') {
            if ($this->deviceType === 'desktop') {
                if ($this->anyCampaignHasVariant($campaigns, VariantType::RectangleXl, onlyPremium:true)) {
                    return VariantType::RectangleXl;
                }
            }
            return VariantType::Rectangle;
        }
        if ($slotType === 'header') {
            if ($this->deviceType === 'desktop') {
                if (count($campaigns) === 1) {
                    if ($this->anyCampaignHasVariant($campaigns, VariantType::LeaderBoard, false)) {
                        return VariantType::LeaderBoard;
                    }
                }
                if ($this->anyCampaignHasVariant($campaigns, VariantType::LeaderBoardXl, onlyPremium:true)) {
                    return VariantType::LeaderBoardXl;
                }
            }
        }
        if ($slotType === 'feed') {
            if ($this->deviceType === 'mobile') {
                if ($this->anyCampaignHasVariant($campaigns, VariantType::BannerXl, false)) {
                    return VariantType::BannerXl;
                }
            } else {
                if ($this->anyCampaignHasVariant($campaigns, VariantType::Banner, false)) {
                    return VariantType::Banner;
                }
            }
            return VariantType::Rectangle;
        }
        if ($this->deviceType === 'mobile') {
            return VariantType::BannerXl;
        }
        return VariantType::Banner;
    }

    /**
     * @param Campaign[] $campaigns
     */
    private function anyCampaignHasVariant(array $campaigns, VariantType $type, bool $onlyPremium): bool {
        foreach ($campaigns as $campaign) {
            if (!$onlyPremium || $campaign->payload->isPremium) {
                if (!empty($campaign->variantsOfType($type))) {
                    return true;
                }
            }
        }
        return false;
    }

    private function parseVariantType(string $variantType): VariantType {
        return match ($variantType) {
            'banner'         => VariantType::Banner,
            'banner-xl'      => VariantType::BannerXl,
            'leaderboard'    => VariantType::LeaderBoard,
            'leaderboard-xl' => VariantType::LeaderBoardXl,
            'rectangle'      => VariantType::Rectangle,
            'rectangle-xl'   => VariantType::RectangleXl,
            default          => throw new \Exception("Unknown variant type: $variantType")
        };
    }

    public function close(): void {}
}
