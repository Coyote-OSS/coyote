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
    ) {}

    public static function hasType(VariantType $type): callable {
        return fn(self $variant): bool => $variant->payload->type === $type;
    }
}
