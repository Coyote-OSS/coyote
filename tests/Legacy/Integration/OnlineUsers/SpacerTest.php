<?php
namespace Tests\Legacy\Integration\OnlineUsers;

use Coyote\Domain\Spacer;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class SpacerTest extends TestCase
{
    #[Test]
    public function spaceNone(): void
    {
        $spacer = new Spacer(1);
        [$items, $remaining] = $spacer->fitInSpace([]);
        $this->assertSame([], $items);
        $this->assertSame(0, $remaining);
    }

    #[Test]
    public function spacesZero_isInvalid(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Spacer(0);
    }

    #[Test]
    public function spacesNegative_isInvalid(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Spacer(-1);
    }

    #[Test]
    public function spaceOne(): void
    {
        $spacer = new Spacer(1);
        $this->assertSame([['Foo'], 0], $spacer->fitInSpace(['Foo']));
    }

    #[Test]
    public function spaceMany(): void
    {
        $spacer = new Spacer(2);
        $this->assertSame([['Foo', 'Bar'], 0], $spacer->fitInSpace(['Foo', 'Bar']));
    }

    #[Test]
    public function overflowTwo(): void
    {
        $spacer = new Spacer(1);
        $this->assertSame([[], 2], $spacer->fitInSpace(['Foo', 'Bar']));
    }

    #[Test]
    public function overflowThree(): void
    {
        $spacer = new Spacer(2);
        $this->assertSame([['Foo'], 2], $spacer->fitInSpace(['Foo', 'Bar', 'Cat']));
    }

    #[Test]
    public function overflowFour(): void
    {
        $spacer = new Spacer(3);
        $this->assertSame([['Foo', 'Bar'], 2], $spacer->fitInSpace(['Foo', 'Bar', 'Cat', 'Door']));
    }
}
