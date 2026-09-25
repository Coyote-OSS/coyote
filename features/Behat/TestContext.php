<?php
namespace Features\Behat;

use Features\Dsl\Driver\Channel\AcceptanceChannel\BrowserDriver;

/**
 * State shared by all scenarios of the suite. Starting a browser is the slowest
 * part of a scenario, so a single browser is shared by all scenarios, and closed
 * after the suite.
 */
readonly class TestContext {
    public \DateTimeImmutable $testStartDate;
    private ?BrowserDriver $browserDriver;

    public function __construct(
        public string $baseUrl,
        string        $userAgent,
        bool          $openBrowser,
    ) {
        $this->testStartDate = new \DateTimeImmutable();
        $this->browserDriver = $openBrowser ? new BrowserDriver($baseUrl, $userAgent) : null;
    }

    public function finalize(): void {
        $this->browserDriver?->close();
    }

    public function browserDriver(): BrowserDriver {
        return $this->browserDriver;
    }
}
