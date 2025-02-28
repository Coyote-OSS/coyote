<?php
namespace Neon\Test\Integration;

use PHPUnit\Framework\TestCase;

readonly class Dsl
{
    public Driver $driver;

    public function __construct()
    {
        $this->driver = new Driver();
        $this->driver->clearClientData();
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
