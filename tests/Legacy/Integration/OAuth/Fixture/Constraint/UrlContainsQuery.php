<?php
namespace Tests\Legacy\Integration\OAuth\Fixture\Constraint;

use PHPUnit\Framework\Constraint\Constraint;
use SebastianBergmann\Comparator\ComparisonFailure;

class UrlContainsQuery extends Constraint
{
    public function __construct(private array $query)
    {
    }

    public function matches($other): bool
    {
        if (\is_string($other)) {
            return $this->queryStringsMatch($other);
        }
        return false;
    }

    private function queryStringsMatch(string $uri): bool
    {
        return $this->query === \array_intersect_key($this->queryParams($uri), $this->query);
    }

    private function queryParams(string $uri): array
    {
        \parse_str($this->queryString($uri), $params);
        return $params;
    }

    private function queryString(string $uri): mixed
    {
        $components = \parse_url($uri);
        return $components['query'] ?? '';
    }

    public function toString(): string
    {
        return 'has query params ' . $this->exporter()->export($this->query);
    }

    protected function fail($other, $description, ComparisonFailure $comparisonFailure = null): never
    {
        parent::fail($other, $description,
            new ComparisonFailure(null, null,
                $this->exporter()->export(\http_build_query($this->query)),
                $this->exporter()->export($other)));
    }
}
