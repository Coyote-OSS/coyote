<?php
namespace Test\Libs\Arrays;

use Libs\Arrays\arrays;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ArrayMapTest extends TestCase {
    #[Test]
    public function acceptEmptyArray(): void {
        $this->assertArrayEmpty([] |> arrays::map(fn() => null));
    }

    #[Test]
    public function mapItems(): void {
        $this->assertArrayEquals(
            [5, 10],
            [1, 2] |> arrays::map(fn($item) => $item * 5));
    }

    private function assertArrayEmpty(array $actual): void {
        $this->assertArrayEquals([], $actual);
    }

    private function assertArrayEquals(array $expected, array $actual): void {
        $this->assertSame($expected, $actual);
    }
}
