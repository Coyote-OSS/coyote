<?php

namespace Tests\Legacy\LaravelIntegration\Services;

use Tests\Legacy\LaravelIntegration\TestCase;

class GeoIpTest extends TestCase
{
    /**
     * @var \Coyote\Services\GeoIp\GeoIp
     */
    protected $geoIp;

    protected function setUp(): void
    {
        parent::setUp();
        $this->markTestSkipped();

        $this->geoIp = app('geo-ip');
    }

    // tests
    public function testGeocodeCity()
    {
        $result = $this->geoIp->city('Wrocław');
        $this->assertGreaterThan(0, count($result));

        $first = array_first($result);
        $this->assertEquals('Wrocław', $first['name']);
    }

    public function testGeocodeAsciiName()
    {
        $result = $this->geoIp->city('Wroclaw');
        $this->assertGreaterThan(0, count($result));

        $first = array_first($result);
        $this->assertEquals('Wrocław', $first['name']);
    }

    public function testNormalizeByLocale()
    {
        $normalize = new \Coyote\Services\GeoIp\Normalizers\Locale('pl');

        $result = $normalize->normalize($this->geoIp->city('Wroclaw'));
        $this->assertEquals('Wrocław', $result['name']);

        $result = $normalize->normalize($this->geoIp->city('Warsaw'));
        $this->assertEquals('Warszawa', $result['name']);

        $result = $normalize->normalize($this->geoIp->city('Gdansk'));
        $this->assertEquals('Gdańsk', $result['name']);

        $result = $normalize->normalize($this->geoIp->city('Krakow'));
        $this->assertEquals('Kraków', $result['name']);

        $result = $normalize->normalize($this->geoIp->city('Warszawa'));
        $this->assertEquals('Warszawa', $result['name']);

        $result = $normalize->normalize($this->geoIp->city('Wrocław'));
        $this->assertEquals('Wrocław', $result['name']);

        $result = $normalize->normalize($this->geoIp->city('Gdańsk'));
        $this->assertEquals('Gdańsk', $result['name']);

        $result = $normalize->normalize($this->geoIp->city('Berlin'));
        $this->assertEquals('Berlin', $result['name']);
    }

    public function testGeocodeNonExistingCityAndThrowException()
    {
        $this->expectException('GuzzleHttp\Exception\ClientException');
        $this->geoIp->city('zyc123');
    }

    public function testGeocodeIp()
    {
        $result = $this->geoIp->ip('104.16.34.249');
        $this->assertEquals('US', $result['country_code']);

        $this->assertFalse($this->geoIp->ip('192.168.0.1'));
    }

    public function testGeocodeIpv6Ip()
    {
        $this->assertFalse($this->geoIp->ip('2a03:1280:0000:0252:68af:97ac:749c:b439'));
    }
}
