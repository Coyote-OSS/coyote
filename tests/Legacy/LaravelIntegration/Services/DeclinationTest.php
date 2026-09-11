<?php

namespace Tests\Legacy\LaravelIntegration\Services;

use Coyote\Services\Declination;
use Tests\Legacy\LaravelIntegration\TestCase;

class DeclinationTest extends TestCase
{
    private function getSeconds($value)
    {
        return Declination::format($value, ['sekunda', 'sekundy', 'sekund']);
    }

    private function getApplication($value)
    {
        return Declination::format($value, ['aplikacja', 'aplikacje', 'aplikacji']);
    }

    private function getViews($value)
    {
        return Declination::format($value, ['odsłona', 'odsłony', 'odsłon']);
    }

    private function getPoints($value)
    {
        return Declination::format($value, ['punkt', 'punkty', 'punktów']);
    }

    // tests
    public function testIfDeclinationReturnsCorrectFormatOfSeconds()
    {
        $this->assertSame('1 sekunda', $this->getSeconds(1));
        $this->assertSame('2 sekundy', $this->getSeconds(2));
        $this->assertSame('3 sekundy', $this->getSeconds(3));
        $this->assertSame('10 sekund', $this->getSeconds(10));
        $this->assertSame('11 sekund', $this->getSeconds(11));
        $this->assertSame('99 sekund', $this->getSeconds(99));
        $this->assertSame('100 sekund', $this->getSeconds(100));
        $this->assertSame('0 sekund', $this->getSeconds(0));
    }

    public function testDeclination()
    {
        $this->assertSame('0 aplikacji', $this->getApplication(0));
        $this->assertSame('1 aplikacja', $this->getApplication(1));
        $this->assertSame('2 aplikacje', $this->getApplication(2));
        $this->assertSame('3 aplikacje', $this->getApplication(3));
        $this->assertSame('27 aplikacji', $this->getApplication(27));

        $this->assertSame('0 odsłon', $this->getViews(0));
        $this->assertSame('1 odsłona', $this->getViews(1));
        $this->assertSame('2 odsłony', $this->getViews(2));
        $this->assertSame('27 odsłon', $this->getViews(27));

        $this->assertSame('0 punktów', $this->getPoints(0));
        $this->assertSame('1 punkt', $this->getPoints(1));
        $this->assertSame('2 punkty', $this->getPoints(2));
        $this->assertSame('22 punkty', $this->getPoints(22));
        $this->assertSame('1000 punktów', $this->getPoints(1000));
    }
}
