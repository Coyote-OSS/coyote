<?php
namespace Modules\Campaigns;

interface ForPriviligedUsers {
    public function userHasHighReputation(): bool;

    public function userIsSponsor(): bool;

    public function userIsRobot(): bool;
}
