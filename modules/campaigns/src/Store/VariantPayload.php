<?php
namespace Modules\Campaigns\Store;

readonly class VariantPayload {
    public function __construct(
        public string $bannerType,
        public string $imageUrl,
    ) {}
}
