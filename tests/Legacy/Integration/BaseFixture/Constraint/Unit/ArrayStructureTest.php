<?php
namespace Tests\Legacy\Integration\BaseFixture\Constraint\Unit;

use PHPUnit\Framework\TestCase;
use Tests\Legacy\Integration\BaseFixture\Constraint;
use Tests\Legacy\Integration\BaseFixture\Constraint\ArrayStructure;
use Tests\Legacy\Integration\BaseFixture\Constraint\TrimmedString;
use Tests\Legacy\Integration\BaseFixture\Constraint\UrlPathEquals;

class ArrayStructureTest extends TestCase
{
    use Constraint\Unit\Fixture\Assertion;

    public function test()
    {
        $constraint = new ArrayStructure([
            'other' => $this->identicalTo('value'),
            'key'   => new TrimmedString('foo'),
        ]);

        $this->assertAccepts($constraint, ['key' => '  foo  ', 'other' => 'value']);
    }

    /**
     * @test
     */
    public function rejectAll()
    {
        $constraint = new ArrayStructure([
            'key' => 'value', 'missing' => 'missing',
        ]);

        $this->assertRejects($constraint, ['key' => 'value']);
    }

    /**
     * @test
     */
    public function rejectMessageStringLiteral()
    {
        $constraint = new ArrayStructure(['key' => 'value']);

        $this->assertRejectsMessage($constraint, ['key' => 'other'],
            "Failed asserting that value at 'key' is identical to 'value'.");
    }

    /**
     * @test
     */
    public function rejectMessageIdenticalTo()
    {
        $constraint = new ArrayStructure(['key' => $this->identicalTo('value')]);

        $this->assertRejectsMessage($constraint, ['key' => 'other'],
            "Failed asserting that value at 'key' is identical to 'value'.");
    }

    /**
     * @test
     */
    public function rejectMessageTrimmed()
    {
        $constraint = new ArrayStructure(['foo' => new TrimmedString('value')]);

        $this->assertRejectsMessage($constraint, ['foo' => '  bar  '],
            "Failed asserting that value at 'foo' trimmed is 'value'.");
    }

    /**
     * @test
     */
    public function rejectMessageRelativeUri()
    {
        $constraint = new ArrayStructure(['key' => new UrlPathEquals('ftp://host', '/foo')]);

        $this->assertRejectsMessage($constraint, ['key' => '/bar'],
            "Failed asserting that value at 'key' is relative uri of 'ftp://host/foo'.");
    }

    /**
     * @test
     */
    public function rejectMessageOnlyFailed()
    {
        $constraint = new ArrayStructure([
            'one'   => '111',
            'two'   => '222',
            'three' => '333',
            'four'  => '444',
        ]);

        $this->assertRejectsMessage($constraint,
            ['two' => '222', 'four' => '444'],
            "Failed asserting that value at 'one' is identical to '111' and value at 'three' is identical to '333'.
In fact, keys ['one', 'three'] are not even present.");
    }

    /**
     * @test
     */
    public function rejectLogicalAnd()
    {
        $constraint = new ArrayStructure([
            'key' => 'value',
            'foo' => 'bar',
        ]);

        $this->assertRejectsMessage($this->logicalAnd($constraint), ['key' => 'other'],
            "Failed asserting that Array &0 [
    'key' => 'other',
] value at 'key' is identical to 'value' and value at 'foo' is identical to 'bar'.");
    }

    /**
     * @test
     */
    public function rejectCompareActual()
    {
        $constraint = new ArrayStructure(['key' => 'value']);

        $this->assertRejectsActual(
            $constraint,
            ['one' => '11x1', 'two' => '222', 'three' => '333'],
            "Array &0 [
    'one' => '11x1',
    'two' => '222',
    'three' => '333',
]");
    }

    /**
     * @test
     */
    public function rejectCompareExpectedIdentity()
    {
        $constraint = new ArrayStructure([
            'one'   => '111',
            'two'   => '222',
            'three' => '333',
        ]);

        $this->assertRejectsExpected($constraint, [], "Array &0 (
    'one' => '111'
    'two' => '222'
    'three' => '333'
)");
    }

    /**
     * @test
     */
    public function rejectCompareExpectedMany()
    {
        $constraint = new ArrayStructure([
            'one'   => '111',
            'two'   => $this->identicalTo('222'),
            'three' => new TrimmedString('333'),
            'four'  => new UrlPathEquals('ftp://host/', '444'),
        ]);

        $this->assertRejectsExpected($constraint, [], "Array &0 (
    'one' => '111'
    'two' => '222'
    'three' => trimmed is '333'
    'four' => is relative uri of 'ftp://host/444'
)");
    }

    /**
     * @test
     */
    public function rejectCompareExpectedArgumentOrder()
    {
        $constraint = new ArrayStructure([
            'one'   => '111',
            'two'   => $this->identicalTo('222'),
            'three' => new UrlPathEquals('http://host', '/333'),
            'four'  => new TrimmedString('444'),
        ]);

        $argument = [
            'two'  => '2',
            'four' => '3',
            'one'  => '1',
            'five' => '5',
        ];

        $this->assertRejectsExpected($constraint, $argument, "Array &0 (
    'two' => '222'
    'four' => trimmed is '444'
    'one' => '111'
    'three' => is relative uri of 'http://host/333'
)");
    }

    /**
     * @test
     */
    public function rejectMessageKeyMissing()
    {
        $constraint = new ArrayStructure(['key' => $this->identicalTo('value')]);

        $this->assertRejectsMessage($constraint, ['other' => 'other'],
            "Failed asserting that value at 'key' is identical to 'value'.
In fact, key 'key' is not even present.");
    }

    /**
     * @test
     */
    public function rejectMessageKeyMissingMany()
    {
        $constraint = new ArrayStructure(['one' => '1', 'two' => '2', 'three' => '3']);

        $this->assertRejectsMessage($constraint, ['one' => '1'],
            "Failed asserting that value at 'two' is identical to '2' and value at 'three' is identical to '3'.
In fact, keys ['two', 'three'] are not even present.");
    }
}
