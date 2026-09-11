<?php
namespace Tests\Legacy\Integration\OAuth\Fixture\Constraint;

use PHPUnit\Framework\Constraint\Constraint;
use SebastianBergmann\Comparator\ComparisonFailure;

class UrlBasepath extends Constraint
{
    public function __construct(private string $basePath)
    {
    }

    public function matches($other): bool
    {
        if (\is_string($other)) {
            return $this->hasBasePath($other);
        }
        return false;
    }

    private function hasBasePath(string $other): bool
    {
        return $this->basePath === $this->basePath($other);
    }

    private function basePath(string $url): string
    {
        $components = \parse_url($url);
        $schema = $components['scheme'] ?? '';
        $host = $components['host'] ?? '';
        $path = $components['path'] ?? '';
        return "$schema://$host" . $path;
    }

    public function toString(): string
    {
        return 'has basepath ' . $this->exporter()->export($this->basePath);
    }

    protected function fail($other, $description, ComparisonFailure $comparisonFailure = null): never
    {
        parent::fail($other, $description,
            new ComparisonFailure(null, null,
                $this->exporter()->export($this->basePath),
                $this->exporter()->export($other)));
    }
}
