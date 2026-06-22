<?php
namespace Modules\Campaigns;

readonly class VariantPayload {
    public function __construct(
        public string $bannerType,
        public string $imageUrl,
    ) {}
}
