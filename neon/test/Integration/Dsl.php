<?php
namespace Neon\Test\Integration;

use Neon\Test\Internal\WebDriver;
use PHPUnit\Framework\TestCase;

readonly class Dsl
{
    private Driver $driver;
    public DesignSystemDsl $designSystem;

    public function __construct()
    {
        $web = new WebDriver();
        $this->driver = new Driver($web);
        $this->driver->clearClientData();
        $this->designSystem = new DesignSystemDsl($web);
    }

    public function close(): void
    {
        $this->driver->close();
    }

    public function includeDiagnosticArtifact(TestCase $testCase): void
    {
        $this->driver->includeDiagnosticArtifact($testCase->name());
    }
}
