<?php
namespace Modules\Campaigns;

interface ForRedirectUrls {
    public function redirectUrl(int $variantId): string;
}
