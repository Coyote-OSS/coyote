<?php
namespace Modules\Campaigns\Internal;

use Modules\Campaigns\VariantType;

/**
 * @deprecated
 */
readonly class CampaignBanner {
    public function __construct(
        public string      $bannerUrl,
        public string      $campaignKey,
        public VariantType $type,
        public int         $variantId,
    ) {}
}
