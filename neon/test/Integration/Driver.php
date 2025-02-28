<?php
namespace Neon\Test\Integration;

use Neon\Test\Internal\WebDriver;

readonly class Driver
{
    private string $diagnosticArtifactPath;

    public function __construct(private WebDriver $web)
    {
        $this->diagnosticArtifactPath = '/var/www/neon/test/Integration/';
    }

    public function clearClientData(): void
    {
        $this->web->clearCookies();
    }

    public function close(): void
    {
        $this->web->close();
    }

    public function includeDiagnosticArtifact(string $testCaseName): void
    {
        $this->web->captureScreenshot($this->diagnosticArtifactPath, $testCaseName);
    }
}
