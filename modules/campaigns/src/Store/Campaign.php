<?php
namespace Modules\Campaigns\Store;

readonly class Campaign {
    /**
     * @param CampaignVariant[] $variants
     */
    public function __construct(
        public int             $id,
        public CampaignPayload $payload,
        public array           $variants,
    ) {}
}
