<?php
namespace Modules\Campaigns;

use Libs\Arrays\arrays;
use Modules\Campaigns\Internal\CampaignBanners;
use Modules\Campaigns\Store\Campaign;
use Modules\Campaigns\Store\CampaignPayload;
use Modules\Campaigns\Store\CampaignsStore;
use Modules\Campaigns\Store\CampaignVariant;

readonly class CampaignService {
    private CampaignBannerSelector $selector;

    public function __construct(
        private ForPriviligedUsers $users,
        ForRotatingBanners         $rotate,
        private ForCurrentDate     $date,
        private CampaignsStore     $store,
        private ForUserVoivodeship $userVoivodeship,
    ) {
        $this->selector = new CampaignBannerSelector($rotate);
    }

    public function campaignBanners(DeviceType $device): CampaignBanners {
        if ($this->isCampaignBannersDisabled()) {
            return $this->disabledCampaignBanners();
        }
        return $this->enabledCampaignBanners($device);
    }

    private function isCampaignBannersDisabled(): bool {
        return $this->users->userHasHighReputation()
            || $this->users->userIsSponsor()
            || $this->users->userIsRobot();
    }

    private function disabledCampaignBanners(): CampaignBanners {
        return new CampaignBanners([], null, []);
    }

    private function enabledCampaignBanners(DeviceType $device): CampaignBanners {
        $campaigns = $this->listActiveCampaigns();
        $horizontalType = $this->horizontalVariantType($device);
        $feedType = $this->anyCampaignHasVariant($campaigns, $horizontalType) ? $horizontalType : VariantType::Rectangle;
        return new CampaignBanners(
            $this->selector->campaignBanners($campaigns, $device, $horizontalType, 2),
            $this->selector->campaignBanners($campaigns, $device, VariantType::Rectangle, 1)[0] ?? null,
            $this->selector->feedBanners($campaigns, $feedType, 2));
    }

    private function horizontalVariantType(DeviceType $device): VariantType {
        return $device === DeviceType::Desktop ? VariantType::Banner : VariantType::BannerXl;
    }

    /**
     * @param Campaign[] $campaigns
     */
    private function anyCampaignHasVariant(array $campaigns, VariantType $type): bool {
        return \array_any($campaigns, fn(Campaign $campaign) => !empty($campaign->variantsOfType($type)));
    }

    /**
     * @return Campaign[]
     */
    private function listActiveCampaigns(): array {
        return $this->store->listCampaigns()
                |> arrays::filter($this->isCampaignObjectActive(...))
                |> arrays::filter($this->isCampaignVisibleForVoivodeship(...));
    }

    private function isCampaignVisibleForVoivodeship(Campaign $campaign): bool {
        $campaignVoivodeship = $campaign->payload->voivodeship;
        if ($campaignVoivodeship === null) {
            return true;
        }
        return $campaignVoivodeship === $this->userVoivodeship->currentUserVoivodeship();
    }

    public function campaignStatus(int $campaignId): string {
        return $this->campaignObjectStatus($this->store->findCampaign($campaignId));
    }

    private function isCampaignObjectActive(Campaign $campaign): bool {
        return $this->campaignObjectStatus($campaign) === 'active';
    }

    private function campaignObjectStatus(Campaign $campaign): string {
        return $this->campaignPayloadStatus(
            $campaign->payload,
            $this->campaignTotalViewCount($campaign));
    }

    private function campaignPayloadStatus(CampaignPayload $payload, int $campaignTotalViewCount): string {
        if (!$this->hasTarget($payload)) {
            return 'misconfigured';
        }
        if ($payload->targetViews !== null) {
            if ($campaignTotalViewCount >= $payload->targetViews) {
                return 'target-reached';
            }
        }
        if ($payload->activeSinceDate !== null) {
            if (!$this->date->hasStarted($payload->activeSinceDate)) {
                return 'not-started';
            }
        }
        if ($payload->activeUntilDate !== null) {
            if (!$this->date->hasNotFinished($payload->activeUntilDate)) {
                return 'finished';
            }
        }
        return 'active';
    }

    private function hasTarget(CampaignPayload $campaign): bool {
        $hasViewTarget = $campaign->targetViews !== null;
        $hasDateTarget = $campaign->activeUntilDate !== null;
        return $hasViewTarget || $hasDateTarget;
    }

    private function campaignTotalViewCount(Campaign $campaign): int {
        return \array_reduce($campaign->variants,
            fn(int $sum, CampaignVariant $variant) => $variant->views + $sum, 0);
    }
}
