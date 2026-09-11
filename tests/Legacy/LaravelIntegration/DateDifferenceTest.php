<?php

namespace Tests\Legacy\LaravelIntegration;

use Carbon\Carbon;
use Coyote\Services\Helper\DateDifference;

class DateDifferenceTest extends \PHPUnit\Framework\TestCase
{
    protected function setUp(): void
    {
        Carbon::setTestNow(new Carbon('2016-01-23 11:53:20'));
    }

    /**
     * @test
     * @dataProvider formats
     */
    public function testFormat(string $format, string $expected): void
    {
        $date = new DateDifference($format, false);
        $this->assertSame(
            $expected,
            $date->format(new Carbon('2016-01-23 11:53:20')));
    }

    public static function formats(): array
    {
        return [
            ['%d-%m-%Y %H:%M', '23-01-2016 11:53'],
            ['%Y-%m-%d %H:%M', '2016-01-23 11:53'],
            ['%m/%d/%y %H:%M', '01/23/16 11:53'],
            ['%d-%m-%y %H:%M', '23-01-16 11:53'],
            ['%d %b %y %H:%M', '23 sty 16 11:53'],
            ['%d %B %Y, %H:%M', '23 styczeń 2016, 11:53'],
        ];
    }
}
