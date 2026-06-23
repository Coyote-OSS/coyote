<?php
namespace Modules\Campaigns\Store;

readonly class CampaignVariant {
    public function __construct(
        public int            $id,
        public int            $views,
        public int            $clicks,
        public VariantPayload $payload,
    ) {}
}
