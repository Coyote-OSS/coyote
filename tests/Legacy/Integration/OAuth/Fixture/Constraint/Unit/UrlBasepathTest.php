<?php
namespace Tests\Legacy\Integration\OAuth\Fixture\Constraint\Unit;

use PHPUnit\Framework\TestCase;
use Tests\Legacy\Integration\BaseFixture\Constraint;
use Tests\Legacy\Integration\OAuth\Fixture\Constraint\UrlBasepath;

class UrlBasepathTest extends TestCase
{
    use Constraint\Unit\Fixture\Assertion;

    public function test()
    {
        $this->assertAccepts(new UrlBasepath('http://host/path'), 'http://host/path?query=param#anchor');
    }

    /**
     * @test
     */
    public function rejectHost()
    {
        $this->assertRejects(new UrlBasepath('http://other/'), 'http://actual/');
    }

    /**
     * @test
     */
    public function rejectProtocol()
    {
        $this->assertRejects(new UrlBasepath('https://host/'), 'http://host/');
    }

    /**
     * @test
     */
    public function rejectPath()
    {
        $this->assertRejects(new UrlBasepath('https://host/expected'), 'http://host/actual');
    }

    /**
     * @test
     */
    public function rejectRelative()
    {
        $this->assertRejects(new UrlBasepath('https://host/expected'), '/expected');
    }

    /**
     * @test
     */
    public function rejectNoPath()
    {
        $this->assertRejects(new UrlBasepath('https://host/expected'), 'https://host');
    }

    /**
     * @test
     */
    public function rejectPartialPathParent()
    {
        $this->assertRejects(new UrlBasepath('https://host/parent/foo'), 'https://host/parent');
    }

    /**
     * @test
     */
    public function rejectPartialPathChild()
    {
        $this->assertRejects(new UrlBasepath('https://host/foo'), 'https://host/foo/child');
    }

    /**
     * @test
     */
    public function message()
    {
        $this->assertRejectsMessage(
            new UrlBasepath('http://expected/'),
            'http://actual/',
            "Failed asserting that 'http://actual/' has basepath 'http://expected/'.");
    }

    /**
     * @test
     */
    public function rejectInteger()
    {
        $this->assertRejects(new UrlBasepath('http://other/'), 14);
    }

    /**
     * @test
     */
    public function rejectBoolean()
    {
        $this->assertRejects(new UrlBasepath('http://other/'), true);
    }

    /**
     * @test
     */
    public function comparison()
    {
        $this->assertRejectsCompare(
            new UrlBasepath('http://other/root'),
            '/relative',
            "'http://other/root'",
            "'/relative'");
    }
}
