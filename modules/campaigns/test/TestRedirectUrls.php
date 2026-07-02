<?php
namespace Test\Modules\Campaigns;

use Modules\Campaigns\ForRedirectUrls;

readonly class TestRedirectUrls implements ForRedirectUrls {
    public function __construct(private string $baseUrl) {}

    public function redirectUrl(int $variantId): string {
        return "$this->baseUrl/$variantId";
    }
}
