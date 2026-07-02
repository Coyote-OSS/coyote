<?php
namespace Modules\Campaigns\Store;

use Libs\Arrays\arrays;
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
        return $this->variants |> arrays::filter(CampaignVariant::hasType($type));
    }
}
