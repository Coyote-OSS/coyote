<?php
namespace Coyote\Modules\Campaigns;

use Coyote\Reputation;

class AuthPriviligedUsers implements \Modules\Campaigns\ForPriviligedUsers {
    public function userHasHighReputation(): bool {
        return auth()->user()?->reputation >= Reputation::NO_ADS;
    }

    public function userIsSponsor(): bool {
        return auth()->user()?->is_sponsor ?? false;
    }
}
