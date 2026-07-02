<?php
namespace Coyote\Modules\Campaigns\Provided;

use Coyote\Reputation;
use Jenssegers\Agent\Agent;

class AuthPriviligedUsers implements \Modules\Campaigns\ForPriviligedUsers {
    public function userHasHighReputation(): bool {
        return auth()->user()?->reputation >= Reputation::NO_ADS;
    }

    public function userIsSponsor(): bool {
        return auth()->user()?->is_sponsor ?? false;
    }

    public function userIsRobot(): bool {
        return new Agent()->isRobot(request()->userAgent());
    }
}
