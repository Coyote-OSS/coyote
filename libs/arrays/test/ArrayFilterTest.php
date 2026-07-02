<?php
namespace Test\Libs\Arrays;

use Libs\Arrays\arrays;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

class ArrayFilterTest extends TestCase {
    #[Test]
    public function acceptEmptyArray(): void {
        $this->assertArrayEmpty([] |> arrays::filter(fn() => null));
    }

    #[Test]
    public function includeItem(): void {
        $this->assertArrayEquals(
            ['foo'],
            ['foo'] |> arrays::filter(fn() => true));
    }

    #[Test]
    public function excludeItem(): void {
        $this->assertArrayEquals(
            [],
            ['foo'] |> arrays::filter(fn() => false));
    }

    #[Test]
    public function filterFunctionReceivesItem(): void {
        $this->assertArrayEquals(
            ['bar'],
            ['foo', 'bar'] |> arrays::filter(fn(string $item) => $item === 'bar'));
    }

    #[Test]
    #[TestWith([0])]
    #[TestWith([1])]
    #[TestWith([''])]
    #[TestWith(['string'])]
    #[TestWith([null])]
    #[TestWith([new \stdClass()])]
    public function throwsOnInvalidFilterResult_stdClass($item): void {
        $this->expectException(\TypeError::class);
        ['foo'] |> arrays::filter((fn() => $item));
    }

    private function assertArrayEmpty(array $actual): void {
        $this->assertArrayEquals([], $actual);
    }

    private function assertArrayEquals(array $expected, array $actual): void {
        $this->assertSame($expected, $actual);
    }
}
