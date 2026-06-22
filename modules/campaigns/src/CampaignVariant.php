<?php
namespace Modules\Campaigns;

readonly class CampaignVariant {
    public function __construct(
        public int            $id,
        public int            $views,
        public int            $clicks,
        public VariantPayload $payload,
    ) {}
}
