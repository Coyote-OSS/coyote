<?php
namespace Features\Behat;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\AfterStepScope;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Hook\AfterScenario;
use Behat\Hook\AfterStep;
use Behat\Hook\BeforeScenario;
use Behat\Hook\BeforeSuite;
use Features\Dsl\Driver\Channel\AcceptanceChannel\AcceptanceDriver;
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

    #[BeforeSuite]
    public static function captureTestStartDate(): void {
        self::$testStartDate = new \DateTimeImmutable();
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
            'acceptance'  => new AcceptanceDriver(self::$testStartDate),
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
            // driver has to capture a diagnostic screenshot and close itself here, or
            // both are silently lost and the browser session leaks into the next scenario.
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
