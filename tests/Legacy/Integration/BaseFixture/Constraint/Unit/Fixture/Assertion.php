<?php
namespace Tests\Legacy\Integration\BaseFixture\Constraint\Unit\Fixture;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\ExpectationFailedException;
use SebastianBergmann\Comparator\ComparisonFailure;

trait Assertion
{
    function assertAccepts(Constraint $constraint, mixed $actual): void
    {
        Assert::assertThat($actual, $constraint);
    }

    function assertRejects(Constraint $constraint, mixed $argument): void
    {
        try {
            $this->execute($constraint, $argument);
        } catch (ExpectationFailedException) {
            Assert::assertTrue(true);
        }
    }

    function assertRejectsCompare(
        Constraint $constraint,
        mixed      $argument,
        ?string    $failExpected,
        ?string    $failActual): void
    {
        $comparison = $this->comparisonFailure($constraint, $argument);
        Assert::assertSame(
            ['expected' => $failExpected, 'actual' => $failActual],
            [
                'expected' => $comparison->getExpectedAsString(),
                'actual'   => $comparison->getActualAsString(),
            ]);
    }

    function assertRejectsMessage(Constraint $constraint, mixed $argument, string $message): void
    {
        Assert::assertSame($message, $this->failException($constraint, $argument)->getMessage());
    }

    function assertRejectsExpected(Constraint $constraint, mixed $argument, string $expected): void
    {
        $comparison = $this->comparisonFailure($constraint, $argument);
        Assert::assertSame($expected, $comparison?->getExpectedAsString());
    }

    function assertRejectsActual(Constraint $constraint, mixed $argument, string $actual): void
    {
        $comparison = $this->comparisonFailure($constraint, $argument);
        Assert::assertSame($actual, $comparison->getActualAsString());
    }

    function comparisonFailure(Constraint $constraint, mixed $argument): ComparisonFailure
    {
        return $this->failException($constraint, $argument)->getComparisonFailure();
    }

    function failException(Constraint $constraint, mixed $argument): ExpectationFailedException
    {
        try {
            $this->execute($constraint, $argument);
        } catch (ExpectationFailedException $exception) {
            return $exception;
        }
    }

    function execute(Constraint $constraint, mixed $argument): void
    {
        Assert::assertThat($argument, $constraint);
        throw new \AssertionError('Failed to assert that constraint was not matched');
    }
}
