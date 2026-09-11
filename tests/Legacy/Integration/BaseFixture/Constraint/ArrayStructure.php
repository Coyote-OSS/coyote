<?php
namespace Tests\Legacy\Integration\BaseFixture\Constraint;

use Override;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\Constraint\IsIdentical;
use SebastianBergmann\Comparator\ComparisonFailure;

class ArrayStructure extends Constraint
{
    /** @var (Constraint|string)[] */
    private array $structure;
    /** @var string[] */
    private array $failedKeys;

    public function __construct(array $structure)
    {
        $this->structure = \array_map($this->constraint(...), $structure);
    }

    private function constraint(mixed $constraint): Constraint
    {
        if ($constraint instanceof Constraint) {
            return $constraint;
        }
        return Assert::identicalTo($constraint);
    }

    protected function matches($other): bool
    {
        $this->failedKeys = $this->failedKeys($other);
        return empty($this->failedKeys);
    }

    private function failedKeys(array $other): array
    {
        $keys = [];
        foreach ($this->structure as $key => $constraint) {
            if (!$this->arrayItemMatches($other, $key)) {
                $keys[] = $key;
            }
        }
        return $keys;
    }

    private function arrayItemMatches(array $array, int|string $key): bool
    {
        if (\array_key_exists($key, $array)) {
            return $this->structure[$key]->evaluate($array[$key], '', true);
        }
        return false;
    }

    protected function failureDescription($other): string
    {
        return $this->toString();
    }

    public function toString(): string
    {
        return \implode(' and ', $this->constraintMessages());
    }

    private function constraintMessages(): array
    {
        $messages = [];
        foreach ($this->structure as $key => $constraint) {
            if (\in_array($key, $this->failedKeys)) {
                $messages[] = "value at {$this->exporter()->export($key)} {$constraint->toString()}";
            }
        }
        return $messages;
    }

    protected function fail($other, $description, ComparisonFailure $comparisonFailure = null): never
    {
        parent::fail($other, $description,
            new ComparisonFailure(null, null,
                $this->expectedAsString($other),
                $this->exporter()->export($other)));
    }

    private function expectedAsString(array $other): string
    {
        $lines = [];
        foreach ($this->structureInArgumentOrder($other) as $key => $value) {
            $lines[] = "    {$this->exporter()->export($key)} => {$this->constraintString($value)}";
        }
        $content = \implode("\n", $lines);
        return "Array &0 (\n$content\n)";
    }

    private function structureInArgumentOrder(array $other): array
    {
        return \array_replace(
            \array_intersect_key($other, $this->structure),
            $this->structure);
    }

    private function constraintString(Constraint $constraint): string
    {
        if ($constraint instanceof IsIdentical) {
            return \subStr($constraint->toString(), \strLen('is identical to '));
        }
        return $constraint->toString();
    }

    #[Override]
    protected function additionalFailureDescription($other): string
    {
        $keys = \array_keys(\array_diff_key($this->structure, $other));

        if (count($keys) === 1) {
            return 'In fact, key ' . $this->formatKey($keys[0]) . ' is not even present.';
        }
        if (count($keys) > 1) {
            return 'In fact, keys ' . $this->formatKeys($keys) . ' are not even present.';
        }
        return '';
    }

    public function formatKey($keys): string
    {
        return $this->exporter()->export($keys);
    }

    public function formatKeys(array $keys): string
    {
        return '[' . implode(', ', \array_map($this->exporter()->export(...), $keys)) . ']';
    }
}
