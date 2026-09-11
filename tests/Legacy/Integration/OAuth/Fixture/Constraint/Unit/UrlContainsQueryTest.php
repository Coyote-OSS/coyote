<?php
namespace Tests\Legacy\Integration\OAuth\Fixture\Constraint\Unit;

use PHPUnit\Framework\TestCase;
use Tests\Legacy\Integration\BaseFixture\Constraint;
use Tests\Legacy\Integration\OAuth\Fixture\Constraint\UrlContainsQuery;

class UrlContainsQueryTest extends TestCase
{
    use Constraint\Unit\Fixture\Assertion;

    public function test()
    {
        $this->assertAccepts(
            new UrlContainsQuery(['foo' => 'bar', 'lorem' => 'ipsum']),
            'http://host/path?foo=bar&lorem=ipsum#anchor');
    }

    /**
     * @test
     */
    public function rejectMissing()
    {
        $this->assertRejects(new UrlContainsQuery(['foo' => 'bar']), 'http://actual/?other=param');
    }

    /**
     * @test
     */
    public function acceptMissing()
    {
        $this->assertAccepts(new UrlContainsQuery([]), 'http://actual/');
    }

    /**
     * @test
     */
    public function acceptSuperfluous()
    {
        $this->assertAccepts(new UrlContainsQuery(['foo' => 'bar']), 'http://actual/?foo=bar&lorem=ipsum');
    }

    /**
     * @test
     */
    public function rejectMismatched()
    {
        $this->assertRejects(new UrlContainsQuery(['one' => '111']), 'http://actual/?two=222');
    }

    /**
     * @test
     */
    public function message()
    {
        $this->assertRejectsMessage(
            new UrlContainsQuery(['foo' => 'bar']),
            'http://actual/',
            "Failed asserting that 'http://actual/' has query params Array &0 [
    'foo' => 'bar',
].");
    }

    /**
     * @test
     */
    public function rejectInteger()
    {
        $this->assertRejects(new UrlContainsQuery([]), 14);
    }

    /**
     * @test
     */
    public function rejectBoolean()
    {
        $this->assertRejects(new UrlContainsQuery([]), true);
    }

    /**
     * @test
     */
    public function comparison()
    {
        $this->assertRejectsCompare(
            new UrlContainsQuery(['foo' => 'bar']),
            'http://host/?one=two',
            "'foo=bar'",
            "'http://host/?one=two'");
    }
}
