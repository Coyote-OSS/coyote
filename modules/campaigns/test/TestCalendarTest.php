<?php
namespace Test\Modules\Campaigns;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(TestCurrentDate::class)]
class TestCalendarTest extends TestCase {
    private TestCurrentDate $calendar;

    #[Before]
    public function initialize(): void {
        $this->calendar = new TestCurrentDate();
    }

    #[Test]
    public function failForUnsetStubbedDate(): void {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Failed to determine range active, current date not set.');
        $this->calendar->isRangeActive('', '');
    }
}
