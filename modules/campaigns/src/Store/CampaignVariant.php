<?php
namespace Modules\Campaigns\Store;

use Modules\Campaigns\VariantType;

readonly class CampaignVariant {
    public function __construct(
        public int            $id,
        public int            $views,
        public int            $clicks,
        public int            $exposures,
        public VariantPayload $payload,
        public bool           $enabled,
    ) {}

    public static function hasEnabledType(VariantType $type): callable {
        return fn(self $variant): bool => $variant->enabled && $variant->payload->type === $type;
    }
}
