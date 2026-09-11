<?php
namespace Tests\Legacy\Integration\OAuth\Fixture\Constraint;

use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Constraint\Constraint;
use SebastianBergmann\Comparator\ComparisonFailure;

class ResponseRedirect extends Constraint
{
    public function __construct(private Constraint $constraint)
    {
    }

    public function matches($other): bool
    {
        if ($other instanceof TestResponse) {
            $other->assertRedirect();
            return $this->constraint->matches($this->location($other));
        }
        return false;
    }

    private function location(TestResponse $response): string
    {
        return $response->headers->get('Location');
    }

    protected function fail($other, $description, ComparisonFailure $comparisonFailure = null): never
    {
        parent::fail($other, $description,
            new ComparisonFailure(null, null,
                $this->exporter()->export($this->constraint->toString()),
                $this->exporter()->export($this->location($other))));
    }

    protected function failureDescription($other): string
    {
        return 'redirect to ' . $this->location($other) . ' ' . $this->toString();
    }

    public function toString(): string
    {
        return $this->constraint->toString();
    }
}
