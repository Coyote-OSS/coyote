<?php
namespace Tests\Legacy\IntegrationNew\OnlineUsers;

use Coyote\Domain\Online\SessionRepository;
use Coyote\Domain\Online\SessionsSnapshot;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Legacy\IntegrationNew\BaseFixture\Server\Laravel;

class SessionsTest extends TestCase {
    use Laravel\Transactional;

    private SessionRepository $sessions;

    #[Before]
    public function initialize(): void {
        $this->sessions = $this->laravel->app->get(SessionRepository::class);
    }

    #[Test]
    public function whenNoUserIsLogged_userList_returnsEmptyList(): void {
        $this->assertEmpty($this->viewers()->users);
    }

    #[Test]
    public function whenUserIsLogged_userList_returnsThatUserId(): void {
        $this->viewAsUser(userId:99);
        $this->assertSame([99], $this->viewers()->users);
    }

    #[Test]
    public function whenManyUsersAreLogged_userList_returnsTheirIds(): void {
        $this->viewAsUser(46);
        $this->viewAsUser(47);
        $this->assertSame([46, 47], $this->viewers()->users);
    }

    #[Test]
    public function searchCrawlerSessions_areNotIncludedInUserList(): void {
        $this->visitSearchCrawler();
        $this->assertSame([], $this->viewers()->users);
    }

    #[Test]
    public function whenGuestIsPresent_userList_notIncludesHim(): void {
        $this->viewAsGuest();
        $this->assertSame([], $this->viewers()->users);
    }

    #[Test]
    public function whenGuestIsPresent_guestsCount_includesHim(): void {
        $this->viewAsGuest();
        $this->assertSame(1, $this->viewers()->guestsCount);
    }

    #[Test]
    public function whenNoGuestIsPresent_guestsCount_returns0(): void {
        $this->assertSame(0, $this->viewers()->guestsCount);
    }

    #[Test]
    public function whenManyGuestsArePresent_guestsCount_returns2(): void {
        $this->viewAsGuest();
        $this->viewAsGuest();
        $this->assertSame(2, $this->viewers()->guestsCount);
    }

    #[Test]
    public function guestsAndUsers_areCountedSeparately(): void {
        $this->viewAsUser(45);
        $this->viewAsUser(46);
        $this->viewAsUser(47);
        $this->viewAsGuest();
        $this->viewAsGuest();
        $this->assertSame(2, $this->viewers()->guestsCount);
    }

    #[Test]
    public function usersAreFilteredBasedOnPrefixPath(): void {
        $this->viewAsUser(userId:44, path:'/foo/bar');
        $this->viewAsUser(userId:55, path:'/bar');
        $snapshot = $this->sessions->sessionsIn('/foo');
        $this->assertSame([44], $snapshot->users);
    }

    #[Test]
    public function guestsAreFilteredBasedOnPathPrefix(): void {
        $this->viewAsGuest(path:'/foo/bar');
        $this->viewAsGuest(path:'/foo/cat');
        $this->viewAsGuest(path:'/bar');
        $snapshot = $this->sessions->sessionsIn('/foo');
        $this->assertSame(2, $snapshot->guestsCount);
    }

    #[Test]
    public function filteringByPath_isCaseInsensitive(): void {
        $this->viewAsUser(userId:44, path:'/foo/bar');
        $snapshot = $this->sessions->sessionsIn('/FOO');
        $this->assertCount(1, $snapshot->users);
    }

    #[Test]
    public function userWithMultipleSessions_isReturnedAsOne(): void {
        $this->viewAsUser(userId:44, path:'/foo/bar');
        $this->viewAsUser(userId:44, path:'/foo/cat');
        $snapshot = $this->sessions->sessionsIn('/foo');
        $this->assertCount(1, $snapshot->users);
    }

    #[Test]
    public function userWithMultipleSessions_areNotCountedAsNonGuest(): void {
        $this->viewAsUser(userId:44);
        $this->viewAsUser(userId:44);
        $this->assertSame(0, $this->viewers()->guestsCount);
    }

    private function viewAsUser(int $userId, ?string $path = null): void {
        $this->laravel->databaseTable('sessions')->insert([
            'id'      => '',
            'robot'   => '',
            'user_id' => $userId,
            'path'    => $path ?? '/',
        ]);
    }

    private function viewAsGuest(?string $path = null): void {
        $this->laravel->databaseTable('sessions')->insert([
            'id'      => '',
            'robot'   => '',
            'user_id' => null,
            'path'    => $path ?? '/',
        ]);
    }

    private function visitSearchCrawler(): void {
        $this->laravel->databaseTable('sessions')->insert([
            'id'    => '',
            'robot' => 'Bot',
        ]);
    }

    private function viewers(): SessionsSnapshot {
        return $this->sessions->sessionsIn('/');
    }
}
