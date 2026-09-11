<?php
namespace Tests\Legacy\Integration\BaseFixture\Constraint;

use PHPUnit\Framework\Constraint\Constraint;
use SebastianBergmann\Comparator\ComparisonFailure;

class TrimmedString extends Constraint
{
    public function __construct(private string $value)
    {
    }

    protected function matches(mixed $other): bool
    {
        if (is_string($other)) {
            return $this->value === \trim($other);
        }
        return false;
    }

    public function toString(): string
    {
        return 'trimmed is ' . $this->exporter()->export($this->value);
    }

    protected function fail($other, $description, ComparisonFailure $comparisonFailure = null): never
    {
        parent::fail($other, $description,
            new ComparisonFailure(null, null,
                $this->exporter()->export($this->value),
                $this->exporter()->export($other)));
    }
}
