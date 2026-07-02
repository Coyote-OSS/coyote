<?php
namespace Coyote\Modules\Campaigns\Provided;

use Modules\Campaigns\ForRedirectUrls;

class RouteRedirectUrls implements ForRedirectUrls {
    public function redirectUrl(int $variantId): string {
        return route('campaigns.click', [$variantId]);
    }
}
