<?php
namespace Test\Modules\Campaigns;

class TestPrivilegedUsers implements \Modules\Campaigns\ForPriviligedUsers {
    private bool $highReputation = false;
    private bool $userIsSponsor = false;
    private bool $userIsRobot = false;

    public function userHasHighReputation(): bool {
        return $this->highReputation;
    }

    public function userIsSponsor(): bool {
        return $this->userIsSponsor;
    }

    public function userIsRobot(): bool {
        return $this->userIsRobot;
    }

    public function setUserHighReputation(bool $highReputation): void {
        $this->highReputation = $highReputation;
    }

    public function setUserSponsor(bool $isSponsor): void {
        $this->userIsSponsor = $isSponsor;
    }

    public function setUserRobot(bool $isRobot): void {
        $this->userIsRobot = $isRobot;
    }
}
