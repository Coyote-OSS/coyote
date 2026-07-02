<?php
namespace Test\Libs\Arrays;

use Libs\Arrays\arrays;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ArrayValuesTest extends TestCase {
    #[Test]
    public function arrayValues(): void {
        $this->assertArrayEquals(
            [1, 2],
            ['foo' => 1, 'bar' => 2] |> arrays::values());
    }

    private function assertArrayEquals(array $expected, array $actual): void {
        $this->assertSame($expected, $actual);
    }
}
