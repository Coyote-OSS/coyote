<?php
namespace Test\Modules\Campaigns\Store;

use Modules\Campaigns\Store\Campaign;
use Modules\Campaigns\Store\CampaignPayload;
use Modules\Campaigns\Store\CampaignsStore;
use Modules\Campaigns\Store\CampaignVariant;
use Modules\Campaigns\Store\VariantPayload;

class InMemoryCampaignsStore implements CampaignsStore {
    /** @var int[] */
    private array $campaignIds = [];
    /** @var CampaignPayload[] */
    private array $campaignPayloads = [];
    /** @var int[][] */
    private array $campaignVariants = [];
    /** @var VariantPayload[] */
    private array $variantPayloads = [];
    /** @var int[] */
    private array $variantViews = [];
    /** @var int[] */
    private array $variantClicks = [];
    private int $campaignIdSeq = 1;
    private int $variantIdSeq = 1;

    public function createCampaign(CampaignPayload $payload): int {
        $newCampaignId = $this->campaignIdSeq++;
        $this->campaignIds[] = $newCampaignId;
        $this->campaignPayloads[$newCampaignId] = $payload;
        $this->campaignVariants[$newCampaignId] = [];
        return $newCampaignId;
    }

    public function listCampaigns(): array {
        return \array_values(\array_map($this->campaignObject(...), $this->campaignIds));
    }

    public function findCampaign(int $campaignId): ?Campaign {
        if ($this->campaignExists($campaignId)) {
            return $this->campaignObject($campaignId);
        }
        return null;
    }

    private function campaignObject(int $campaignId): Campaign {
        return new Campaign(
            $campaignId,
            $this->campaignPayloads[$campaignId],
            $this->campaignVariants($campaignId));
    }

    private function campaignVariants(int $campaignId): array {
        return \array_map(
            $this->campaignVariant(...),
            $this->campaignVariants[$campaignId]);
    }

    private function campaignVariant(int $variantId): CampaignVariant {
        return new CampaignVariant(
            $variantId,
            $this->variantViews[$variantId],
            $this->variantClicks[$variantId],
            $this->variantPayloads[$variantId]);
    }

    public function updateCampaign(int $campaignId, CampaignPayload $payload): bool {
        if ($this->campaignExists($campaignId)) {
            $this->campaignPayloads[$campaignId] = $payload;
            return true;
        }
        return false;
    }

    public function createVariant(int $campaignId, VariantPayload $payload): ?int {
        if (!$this->campaignExists($campaignId)) {
            return null;
        }
        $newVariantId = $this->variantIdSeq++;
        $this->campaignVariants[$campaignId][] = $newVariantId;
        $this->variantClicks[$newVariantId] = 0;
        $this->variantViews[$newVariantId] = 0;
        $this->variantPayloads[$newVariantId] = $payload;
        return $newVariantId;
    }

    public function viewVariant(int $variantId): void {
        $this->variantViews[$variantId]++;
    }

    public function clickVariant(int $variantId): void {
        $this->variantClicks[$variantId]++;
    }

    private function campaignExists(int $campaignId): bool {
        return \in_array($campaignId, $this->campaignIds);
    }

    public function findCampaignRedirectUrl(int $variantId): ?string {
        foreach ($this->listCampaigns() as $campaign) {
            if ($this->campaignHasVariant($campaign, $variantId)) {
                return $campaign->payload->redirectUrl;
            }
        }
        return null;
    }

    private function campaignHasVariant(Campaign $campaign, int $variantId): bool {
        return array_any($campaign->variants,
            fn(CampaignVariant $variant) => $variant->id === $variantId);
    }
}
