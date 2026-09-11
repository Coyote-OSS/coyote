<?php
namespace Tests\Legacy\Integration\OnlineUsers;

use Coyote\Domain\Online\SessionsSnapshot;
use Coyote\Domain\Online\ViewersStore;
use Coyote\Domain\Online\ViewerUser;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Legacy\Integration\BaseFixture\Forum\Models;

class ViewersStoreTest extends TestCase
{
    use Models;

    private ViewersStore $store;

    #[Before]
    public function initialize(): void
    {
        $this->store = new ViewersStore();
    }

    #[Test]
    public function remainsGuestsCount(): void
    {
        $viewer = $this->store->viewers($this->guests(99));
        $this->assertSame(99, $viewer->guestsCount);
    }

    #[Test]
    public function viewerHasUserName(): void
    {
        $id = $this->driver->newUserReturnId('Mark');
        $this->assertSame('Mark', $this->viewer($id)->name);
    }

    #[Test]
    public function viewerHasUserGroupName(): void
    {
        $id = $this->driver->newUserReturnId(groupName:'Admin');
        $this->assertSame('Admin', $this->viewer($id)->groupName);
    }

    #[Test]
    public function viewerHasUserAvatar(): void
    {
        $id = $this->driver->newUserReturnId(photoUrl:'image.png');
        $this->assertSame('image.png', $this->viewer($id)->avatarUrl);
    }

    #[Test]
    public function forUserWithoutGroup_viewerHasGroupNameNull(): void
    {
        $id = $this->driver->newUserReturnId();
        $this->assertNull($this->viewer($id)->groupName);
    }

    #[Test]
    public function deletedUsers_areNotIncluded(): void
    {
        $id = $this->driver->newUserReturnId(deleted:true);
        $this->assertEmpty($this->viewers($id));
    }

    private function guests(int $guestsCount): SessionsSnapshot
    {
        return new SessionsSnapshot([], $guestsCount);
    }

    private function users(array $users): SessionsSnapshot
    {
        return new SessionsSnapshot($users, 0);
    }

    private function viewer(int $id): ViewerUser
    {
        return $this->viewers($id)[0];
    }

    private function viewers(int $id): array
    {
        $viewer = $this->store->viewers($this->users([$id]));
        return $viewer->users;
    }

    #[Test]
    public function orderViewersByLastVisit(): void
    {
        $sessions = $this->users([
            $this->driver->newUserReturnId('Third', visitedAt:'2012-12-12 12:12:12'),
            $this->driver->newUserReturnId('First', visitedAt:'2001-01-01 01:01:01'),
            $this->driver->newUserReturnId('Second', visitedAt:'2005-05-05 05:05:05'),
        ]);
        $this->assertSame(
            ['Third', 'Second', 'First'],
            $this->viewersNames($sessions));
    }

    private function viewersNames(SessionsSnapshot $sessions): array
    {
        $viewers = $this->store->viewers($sessions);
        return \array_column($viewers->users, 'name');
    }
}
