<?php
namespace Tests\Integration\Modules\Campaigns;

use Carbon\Carbon;
use Coyote\Modules\Campaigns\CarbonCurrentDate;
use Modules\Campaigns\ForCurrentDate;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony;

#[CoversClass(CarbonCurrentDate::class)]
class CarbonCampaignsCalendarTest extends TestCase {
    private ForCurrentDate $calendar;
    private $decemberLast = '1999-12-31T00:00:00';
    private $januaryFirst = '2000-01-01T00:00:00';
    private $januaryMiddle = '2000-01-15T00:00:00';
    private $januaryLast = '2000-01-31T00:00:00';
    private $februaryFirst = '2000-02-01T00:00:00';

    #[Before]
    public function initialize(): void {
        $this->calendar = new CarbonCurrentDate();
    }

    #[Test]
    public function givenCurrentDateIsBefore_isNotActiveRange() {
        $this->setCurrentTime($this->decemberLast);
        $this->assertFalse($this->calendar->isRangeActive($this->januaryFirst, $this->januaryLast));
    }

    #[Test]
    public function givenCurrentDateIsAfter_isNotActiveRange(): void {
        $this->setCurrentTime($this->februaryFirst);
        $this->assertFalse($this->calendar->isRangeActive($this->januaryFirst, $this->januaryLast));
    }

    #[Test]
    public function givenCurrentDateIsBetween_isActiveRange(): void {
        $this->setCurrentTime($this->januaryMiddle);
        $this->assertTrue($this->calendar->isRangeActive($this->januaryFirst, $this->januaryLast));
    }

    #[Test]
    public function failForMalformedStartDate(): void {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Failed to determine range active, date malformed.');
        $this->calendar->isRangeActive('malformed', $this->januaryLast);
    }

    #[Test]
    public function failForMalformedAfterDate(): void {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Failed to determine range active, date malformed.');
        $this->calendar->isRangeActive($this->januaryFirst, 'malformed');
    }

    private function setCurrentTime(string $date): void {
        Carbon::setTestNow(Carbon::parse($date));
    }
}
