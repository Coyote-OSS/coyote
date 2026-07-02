<?php
namespace Modules\Campaigns;

readonly class CampaignBanner {
    public function __construct(
        public string $redirectUrl,
        public string $imageUrl,
        public int    $variantId,
    ) {}
}
