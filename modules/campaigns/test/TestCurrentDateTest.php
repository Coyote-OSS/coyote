<?php
namespace Test\Modules\Campaigns;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Test\Modules\Campaigns\Fixture\TestCurrentDate;

#[CoversClass(TestCurrentDate::class)]
class TestCurrentDateTest extends TestCase {
    private TestCurrentDate $currentDate;

    #[Before]
    public function initialize(): void {
        $this->currentDate = new TestCurrentDate();
    }

    #[Test]
    public function failForUnsetStubbedDate_hasFinished(): void {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageIs('Failed to determine range active, current date not set.');
        $this->currentDate->hasNotFinished('', '');
    }

    #[Test]
    public function failForUnsetStubbedDate_hasStarted(): void {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageIs('Failed to determine range active, current date not set.');
        $this->currentDate->hasStarted('', '');
    }
}
