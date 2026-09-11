<?php
namespace Tests\Legacy\Unit\View;

use Coyote\View\FormatNumber;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class FormatNumberLongTest extends TestCase {
    private FormatNumber $format;

    #[Before]
    public function initialize(): void {
        $this->format = new FormatNumber();
    }

    #[Test]
    public function smallNumberIsFormatedAsIs(): void {
        $this->assertSame('12', $this->format->formatLong(12));
    }

    #[Test]
    public function hundredsNumberIsFormatedAsIs(): void {
        $this->assertSame('512', $this->format->formatLong(512));
    }

    #[Test]
    public function thousandIsFormattedWithSpace(): void {
        $this->assertSame('123 456', $this->format->formatLong(123456));
    }
}
