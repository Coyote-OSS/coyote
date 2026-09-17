<?php
namespace Features\Dsl\Driver\Channel\InMemoryChannel;

use Features\Dsl\Driver\Driver;

class InMemoryDriver implements Driver {
    private array $variantUrls = [];
    private string $deviceType = '';
    private int $campaignCount = 0;
    private bool $isPremium = false;
    private int $renderCount = 0;

    public function createCampaign(string $campaign, bool $premium): void {
        $this->campaignCount++;
        $this->isPremium = $premium;
    }

    public function addVariant(string $campaign, string $variantType, string $variantUrl): void {
        $this->variantUrls[$variantType][] = $variantUrl;
    }

    public function resolveVariantsForUser(string $deviceType): void {
        $this->deviceType = $deviceType;
        $this->renderCount++;
    }

    public function variantsForSlot(string $slotType): array {
        $urls = $this->variantUrls[$this->variantType($slotType)] ?? [];
        if ($urls === []) {
            return [];
        }
        $windowSize = min($this->slotWindowSize($slotType), count($urls));
        $result = [];
        for ($i = 0; $i < $windowSize; $i++) {
            $result[] = $urls[($this->renderCount - 1 + $i) % count($urls)];
        }
        return $result;
    }

    private function slotWindowSize(string $slotType): int {
        if ($slotType === 'feed') {
            return 2;
        }
        return 1;
    }

    private function variantType(string $slotType): string {
        if ($slotType === 'square') {
            if ($this->deviceType === 'desktop' && $this->isPremium) {
                if (isset($this->variantUrls['rectangle-xl'])) {
                    return 'rectangle-xl';
                }
            }
            return 'rectangle';
        }
        if ($slotType === 'header') {
            if ($this->deviceType === 'desktop') {
                if ($this->isSoleCampaign()) {
                    if (isset($this->variantUrls['leaderboard'])) {
                        return 'leaderboard';
                    }
                }
                if ($this->isPremium) {
                    if (isset($this->variantUrls['leaderboard-xl'])) {
                        return 'leaderboard-xl';
                    }
                }
            }
        }
        $bannerType = $this->deviceType === 'mobile' ? 'banner-xl' : 'banner';
        if ($slotType === 'feed' && !isset($this->variantUrls[$bannerType])) {
            return 'rectangle';
        }
        return $bannerType;
    }

    private function isSoleCampaign(): bool {
        return $this->campaignCount === 1;
    }

    public function close(): void {}
}
