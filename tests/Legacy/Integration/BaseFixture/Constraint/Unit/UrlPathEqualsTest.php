<?php
namespace Tests\Legacy\Integration\BaseFixture\Constraint\Unit;

use PHPUnit\Framework\TestCase;
use Tests\Legacy\Integration\BaseFixture\Constraint;
use Tests\Legacy\Integration\BaseFixture\Constraint\UrlPathEquals;

class UrlPathEqualsTest extends TestCase
{
    use Constraint\Unit\Fixture\Assertion;

    public function test()
    {
        $this->assertAccepts(
            new UrlPathEquals('http://host', '/abc/def'),
            'http://host/abc/def');
    }

    /**
     * @test
     */
    public function rejectPath()
    {
        $this->assertRejects(
            new UrlPathEquals('http://host', '/foo'),
            'http://host/123');
    }

    /**
     * @test
     */
    public function rejectHost()
    {
        $this->assertRejects(
            new UrlPathEquals('http://host', '/abc'),
            'http://other/abc');
    }

    /**
     * @test
     */
    public function rejectInteger()
    {
        $this->assertRejects(new UrlPathEquals('http://host', '/'), 2);
    }

    /**
     * @test
     */
    public function rejectMessageHttp()
    {
        $this->assertRejectsMessage(
            new UrlPathEquals('http://host', '/abc'),
            'http://other/abc',
            "Failed asserting that 'http://other/abc' is relative uri of 'http://host/abc'.");
    }

    /**
     * @test
     */
    public function rejectMessageHttps()
    {
        $this->assertRejectsMessage(
            new UrlPathEquals('http://host', '/abc'),
            'https://other/abc',
            "Failed asserting that 'https://other/abc' is relative uri of 'http://host/abc'.");
    }

    /**
     * @test
     */
    public function rejectMessageNotAbsolute()
    {
        $this->assertRejectsMessage(
            new UrlPathEquals('http://host', '/abc'),
            '/abc',
            "Failed asserting that '/abc' is relative uri of 'http://host/abc'.
In fact, '/abc' is not an absolute URL at all");
    }

    /**
     * @test
     */
    public function comparison()
    {
        $this->assertRejectsCompare(
            new UrlPathEquals('http://host', '/foo'),
            'http://host/123',
            "'http://host/foo'",
            "'http://host/123'");
    }
}
