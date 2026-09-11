<?php
namespace Tests\Legacy\Integration\SkippingDate;

use Coyote\Domain\DiscreetDate;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class DiscreetDateTest extends TestCase
{
    #[Test]
    public function startOfThisWeek(): void
    {
        $date = new DiscreetDate('2024-11-15 14:34:32');
        $this->assertSame('2024-11-11 00:00:00', $date->startOfThisWeek());
    }

    #[Test]
    public function startOfThisMonth(): void
    {
        $date = new DiscreetDate('2024-11-15 14:34:32');
        $this->assertSame('2024-11-01 00:00:00', $date->startOfThisMonth());
    }

    #[Test]
    public function firstQuarter(): void
    {
        $date = new DiscreetDate('2024-02-15 14:34:32');
        $this->assertSame('2024-01-01 00:00:00', $date->startOfThisQuarter());
    }

    #[Test]
    public function secondQuarter(): void
    {
        $date = new DiscreetDate('2024-05-15 14:34:32');
        $this->assertSame('2024-04-01 00:00:00', $date->startOfThisQuarter());
    }
}
