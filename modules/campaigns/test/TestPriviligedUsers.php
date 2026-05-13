<?php
namespace Test\Modules\Campaigns;

class TestPriviligedUsers implements \Modules\Campaigns\ForPriviligedUsers {
    private bool $highReputation = false;
    private bool $userIsSponsor = false;

    public function userHasHighReputation(): bool {
        return $this->highReputation;
    }

    public function userIsSponsor(): bool {
        return $this->userIsSponsor;
    }

    public function setUserHighReputation(bool $highReputation): void {
        $this->highReputation = $highReputation;
    }

    public function setUserSponsor(bool $isSponsor): void {
        $this->userIsSponsor = $isSponsor;
    }
}
