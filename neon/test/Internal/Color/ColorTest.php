<?php
namespace Neon\Test\Internal\Color;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ColorTest extends TestCase
{
    #[Test]
    public function parseRgbaOpaque(): void
    {
        $this->assertColorHex('rgba(255, 255, 255, 1)', '#ffffff');
    }

    #[Test]
    public function parseRgbaSingleDigit(): void
    {
        $this->assertColorHex('rgba(2, 2, 2, 1)', '#020202');
    }

    #[Test]
    public function parseRgbaBlackTransparent(): void
    {
        $this->assertColorHex('rgba(0, 0, 0, 0)', '#00000000');
    }

    private function assertColorHex(string $inputRgba, string $expectedHex): void
    {
        $this->assertSame($expectedHex, Color::parseRgbaAsHex($inputRgba));
    }
}
