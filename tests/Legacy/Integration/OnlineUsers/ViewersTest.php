<?php
namespace Tests\Legacy\Integration\OnlineUsers;

use Coyote\Domain\Online\Viewers;
use Coyote\Domain\Online\ViewerUser;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ViewersTest extends TestCase
{
    #[Test]
    public function totalCount(): void
    {
        $viewers = new Viewers($this->usersOfSize(2), 3);
        $this->assertSame(5, $viewers->totalCount());
    }

    #[Test]
    public function usersWithGroup(): void
    {
        $viewers = new Viewers([
            $this->user(),
            $this->user(name:'Mark', group:'Blue'),
        ], 0);
        $users = $viewers->usersWithGroup();
        $this->assertCount(1, $users);
        $this->assertSame('Mark', $users[0]->name);
    }

    #[Test]
    public function usersWithoutGroup(): void
    {
        $viewers = new Viewers([
            $this->user(name:'Tom'),
            $this->user(group:'Blue'),
        ], 0);
        $users = $viewers->usersWithoutGroup();
        $this->assertCount(1, $users);
        $this->assertSame('Tom', $users[0]->name);
    }

    private function usersOfSize(int $size): array
    {
        return \array_fill(0, $size, $this->user());
    }

    private function user(?string $name = null, ?string $group = null): ViewerUser
    {
        return new ViewerUser($name ?? '', $group, $group, null, '', 1);
    }
}
