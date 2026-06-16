<?php
namespace Test\Modules\Campaigns;

use Modules\Campaigns\SlidingWindow;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class SlidingWindowTest extends TestCase {
    private SlidingWindow $window;

    #[Before]
    public function initialize(): void {
        $this->window = new SlidingWindow();
    }

    #[Test]
    public function emptyArray(): void {
        $this->assertSame([], $this->window->slide([], 2, 0));
    }

    #[Test]
    public function arraySmallerOrEqualThanLength_returnsSelf(): void {
        $one = $this->window->slide(['a'], 3, 0);
        $two = $this->window->slide(['a', 'b'], 3, 0);
        $three = $this->window->slide(['a', 'b', 'c'], 3, 0);
        $this->assertSame(['a'], $one);
        $this->assertSame(['a', 'b'], $two);
        $this->assertSame(['a', 'b', 'c'], $three);
    }

    #[Test]
    public function arrayBiggerThanLength_returnsSubarray(): void {
        $this->assertSame([],
            $this->window->slide(['a', 'b', 'c'], 0, 0));
        $this->assertSame(['a'],
            $this->window->slide(['a', 'b', 'c'], 1, 0));
        $this->assertSame(['a', 'b'],
            $this->window->slide(['a', 'b', 'c'], 2, 0));
    }

    #[Test]
    public function sizeTwo_lengthTwo_slidesWindow(): void {
        $this->assertSame(['a', 'b'], $this->window->slide(['a', 'b'], 2, 0));
        $this->assertSame(['b', 'a'], $this->window->slide(['a', 'b'], 2, 1));
        $this->assertSame(['a', 'b'], $this->window->slide(['a', 'b'], 2, 2));
        $this->assertSame(['b', 'a'], $this->window->slide(['a', 'b'], 2, 3));
    }

    #[Test]
    public function sizeThree_lengthTwo_slidesWindow(): void {
        $this->assertSame(['a', 'b'], $this->window->slide(['a', 'b', 'c'], 2, 0));
        $this->assertSame(['b', 'c'], $this->window->slide(['a', 'b', 'c'], 2, 1));
        $this->assertSame(['c', 'a'], $this->window->slide(['a', 'b', 'c'], 2, 2));
        $this->assertSame(['a', 'b'], $this->window->slide(['a', 'b', 'c'], 2, 3));
        $this->assertSame(['b', 'c'], $this->window->slide(['a', 'b', 'c'], 2, 4));
    }

    #[Test]
    public function sizeThree_lengthThree_slidesWindow(): void {
        $this->assertSame(['a', 'b', 'c'], $this->window->slide(['a', 'b', 'c'], 3, 0));
        $this->assertSame(['b', 'c', 'a'], $this->window->slide(['a', 'b', 'c'], 3, 1));
        $this->assertSame(['c', 'a', 'b'], $this->window->slide(['a', 'b', 'c'], 3, 2));
        $this->assertSame(['a', 'b', 'c'], $this->window->slide(['a', 'b', 'c'], 3, 3));
        $this->assertSame(['b', 'c', 'a'], $this->window->slide(['a', 'b', 'c'], 3, 4));
    }

    #[Test]
    public function arraySmallerThanLength_slidesWindow(): void {
        $this->assertSame(['a', 'b'], $this->window->slide(['a', 'b'], 4, 0));
        $this->assertSame(['b', 'a'], $this->window->slide(['a', 'b'], 4, 1));
        $this->assertSame(['a', 'b'], $this->window->slide(['a', 'b'], 4, 2));
        $this->assertSame(['b', 'a'], $this->window->slide(['a', 'b'], 4, 3));
    }
}
