<?php
namespace Tests\Legacy\IntegrationNew\BaseFixture\Constraint;

use Override;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Util\Exporter;
use SebastianBergmann\Comparator\ComparisonFailure;

class UrlPathEquals extends Constraint
{
    private readonly string $url;

    public function __construct(string $baseUrl, string $path)
    {
        $this->url = $baseUrl . $path;
    }

    #[Override]
    protected function matches($other): bool
    {
        return $this->url === $other;
    }

    #[Override]
    public function toString(): string
    {
        return "is relative uri of '$this->url'";
    }

    #[Override]
    protected function additionalFailureDescription($other): string
    {
        if ($this->hasScheme($other)) {
            return '';
        }
        $value = Exporter::export($other);
        return "In fact, $value is not an absolute URL at all";
    }

    #[Override]
    protected function fail($other, $description, ComparisonFailure $comparisonFailure = null): never
    {
        parent::fail($other, $description,
            new ComparisonFailure(null, null,
                Exporter::export($this->url),
                Exporter::export($other)));
    }

    private function hasScheme(string $url): bool
    {
        return \array_key_exists('scheme', \parse_url($url));
    }
}
