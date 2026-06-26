<?php
namespace Provided;

use Coyote\Modules\Campaigns\Provided\AuthPriviligedUsers;
use Coyote\Reputation;
use Coyote\User;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Legacy\IntegrationNew\BaseFixture;

#[CoversClass(AuthPriviligedUsers::class)]
class AuthPriviligedUsersTest extends TestCase {
    use BaseFixture\Server\Http;

    private AuthPriviligedUsers $users;

    #[Before]
    public function before(): void {
        $this->users = new AuthPriviligedUsers();
    }

    #[Test]
    public function guest_isNotSponsor(): void {
        $this->assertFalse($this->users->userIsSponsor());
    }

    #[Test]
    public function loggedSponsoredUser_isSponsor(): void {
        $this->server->login($this->newUser(isSponsor:true));
        $this->assertTrue($this->users->userIsSponsor());
    }

    #[Test]
    public function loggedRegularUser_isNotSponsor(): void {
        $this->server->login($this->newUser(isSponsor:false));
        $this->assertFalse($this->users->userIsSponsor());
    }

    #[Test]
    public function guest_doesNot_haveHighReputation(): void {
        $this->assertFalse($this->users->userHasHighReputation());
    }

    #[Test]
    public function loggedUserWithZeroReputation_doesNotHaveHighReputation(): void {
        $this->server->login($this->newUser(reputation:0));
        $this->assertFalse($this->users->userHasHighReputation());
    }

    #[Test]
    public function loggedUserWithNotQuiteReputation_doesNotHaveHighReputation(): void {
        $this->server->login($this->newUser(reputation:Reputation::NO_ADS - 1));
        $this->assertFalse($this->users->userHasHighReputation());
    }

    #[Test]
    public function loggedUserWithExactlyReputation_haveHighReputation(): void {
        $this->server->login($this->newUser(reputation:Reputation::NO_ADS));
        $this->assertTrue($this->users->userHasHighReputation());
    }

    #[Test]
    public function loggedUserWithHigherReputation_haveHighReputation(): void {
        $this->server->login($this->newUser(reputation:Reputation::NO_ADS + 1));
        $this->assertTrue($this->users->userHasHighReputation());
    }

    #[Test]
    public function sanityCheck(): void {
        $this->assertEquals(10000, Reputation::NO_ADS);
    }

    private function newUser(
        ?bool $isSponsor = null,
        ?int  $reputation = null,
    ): User {
        $user = new User();
        $user->name = 'irrelevant' . \uniqId();
        $user->email = 'irrelevant';
        $user->is_sponsor = $isSponsor ?? false;
        $user->reputation = $reputation ?? 0;
        $user->save();
        return $user;
    }
}
