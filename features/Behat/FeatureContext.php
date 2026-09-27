<?php
namespace Features\Behat;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\AfterStepScope;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Hook\AfterScenario;
use Behat\Hook\AfterStep;
use Behat\Hook\AfterSuite;
use Behat\Hook\BeforeScenario;
use Behat\Hook\BeforeSuite;
use Features\Dsl\Driver\Channel\AcceptanceChannel\AcceptanceDriver;
use Features\Dsl\Driver\Channel\AcceptanceChannel\BrowserConnection;
use Features\Dsl\Driver\Channel\InMemoryChannel\InMemoryDriver;
use Features\Dsl\Driver\Channel\IntegrationChannel\IntegrationDriver;
use Features\Dsl\Driver\Driver;

/**
 * @noinspection PhpUnused
 */
class FeatureContext implements Context {
    use CampaignSteps;
    use JobOfferSteps;

    private static \DateTimeImmutable $testStartDate;
    private static ?BrowserConnection $browserConnection = null;

    #[BeforeSuite]
    public static function initializeSuite(): void {
        self::$testStartDate = new \DateTimeImmutable();
        if (\getEnv('TEST_CHANNEL') === 'acceptance') {
            self::$browserConnection = new BrowserConnection(
                'http://nginx',
                self::userAgentNonCrawler());
            self::$browserConnection->initialize();
        }
    }

    #[AfterSuite]
    public static function finalizeSuite(): void {
        self::$browserConnection?->finalize();
    }

    private static function userAgentNonCrawler(): string {
        return 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36';
    }

    private Driver $driver;
    private Assertion $assert;

    public function __construct() {
        $this->driver = $this->createDriver();
        $this->assert = new Assertion();
    }

    private function createDriver(): Driver {
        return match (\getEnv('TEST_CHANNEL')) {
            'in-memory'   => InMemoryDriver::create(),
            'integration' => new IntegrationDriver(),
            'acceptance'  => new AcceptanceDriver(self::$testStartDate, self::$browserConnection->browser()),
            default       => throw new \Error('Failed to resolve the test channel.'),
        };
    }

    #[BeforeScenario]
    public function initializeDriver(BeforeScenarioScope $scope): void {
        try {
            $this->driver->initialize(
                $scope->getFeature()->getTitle(),
                $scope->getScenario()->getName());
        } catch (\Throwable $throwable) {
            // Behat does not run AfterScenario hooks when BeforeScenario fails, so the
            // driver has to capture a diagnostic screenshot and finalize itself here, or
            // both are silently lost and the scenario's state leaks into the next scenario.
            $this->driver->captureDiagnostics($scope->getScenario()->getName());
            $this->driver->finalize();
            throw $throwable;
        }
    }

    #[AfterScenario]
    public function finalizeDriver(): void {
        $this->driver->finalize();
    }

    #[AfterStep]
    public function screenshotOnFailure(AfterStepScope $scope): void {
        if (!$scope->getTestResult()->isPassed()) {
            $this->driver->captureDiagnostics($scope->getStep()->getText());
        }
    }
}
