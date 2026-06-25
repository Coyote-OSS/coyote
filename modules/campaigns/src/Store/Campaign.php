<?php
namespace Modules\Campaigns\Store;

use Modules\Campaigns\VariantType;

readonly class Campaign {
    /**
     * @param CampaignVariant[] $variants
     */
    public function __construct(
        public int             $id,
        public CampaignPayload $payload,
        public array           $variants,
    ) {}

    /**
     * @return CampaignVariant[]
     */
    public function variantsOfType(VariantType $type): array {
        return \array_values(array_filter(
            $this->variants,
            fn($variant) => $variant->payload->type === $type));
    }
}
