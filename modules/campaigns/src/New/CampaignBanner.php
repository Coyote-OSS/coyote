<?php
namespace Modules\Campaigns\New;

readonly class CampaignBanner {
    public function __construct(
        public string $redirectUrl,
        public string $imageUrl,
    ) {}
}
