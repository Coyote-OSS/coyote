<?php
namespace Tests\Legacy\Integration\OnlineUsers;

use Coyote\Domain\Online\SessionsSnapshot;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class CoalesceTest extends TestCase
{
    #[Test]
    public function ifUserIsNotPresent_includeHim(): void
    {
        $viewers = new SessionsSnapshot([1, 2], 0);
        $coalesced = $viewers->coalesceUser(3);
        $this->assertSame([1, 2, 3], $coalesced->users);
    }

    #[Test]
    public function ifUserIsPresent_remainHim(): void
    {
        $viewers = new SessionsSnapshot([1, 2], 0);
        $coalesced = $viewers->coalesceUser(2);
        $this->assertSame([1, 2], $coalesced->users);
    }

    #[Test]
    public function whenCoalescingUser_maintainGuestCountMany(): void
    {
        $viewers = new SessionsSnapshot([], 123);
        $coalesced = $viewers->coalesceUser(1);
        $this->assertSame(123, $coalesced->guestsCount);
    }

    #[Test]
    public function whenCoalescingUser_maintainGuestCountNone(): void
    {
        $viewers = new SessionsSnapshot([], 0);
        $coalesced = $viewers->coalesceUser(1);
        $this->assertSame(0, $coalesced->guestsCount);
    }

    #[Test]
    public function ifGuestIsNotPresent_includeHim(): void
    {
        $viewers = new SessionsSnapshot([], 0);
        $this->assertSame(1, $viewers->coalesceGuest()->guestsCount);
    }

    #[Test]
    public function ifGuestIsPresent_remainHim(): void
    {
        $viewers = new SessionsSnapshot([], 3);
        $this->assertSame(3, $viewers->coalesceGuest()->guestsCount);
    }

    #[Test]
    public function whenCoalescingRemainingGuest_maintainUser(): void
    {
        $viewers = new SessionsSnapshot([1], 3);
        $this->assertSame([1], $viewers->coalesceGuest()->users);
    }

    #[Test]
    public function whenCoalescingIncludingGuest_maintainUser(): void
    {
        $viewers = new SessionsSnapshot([1], 0);
        $this->assertSame([1], $viewers->coalesceGuest()->users);
    }
}
