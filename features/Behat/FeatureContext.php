<?php
namespace Features\Behat;

use Behat\Behat\Context\Context;
use Behat\Hook\AfterScenario;
use Features\Dsl\Driver\Channel\AcceptanceChannel\AcceptanceDriver;
use Features\Dsl\Driver\Channel\InMemoryChannel\InMemoryDriver;
use Features\Dsl\Driver\Channel\IntegrationChannel\IntegrationDriver;
use Features\Dsl\Driver\Driver;

/**
 * @noinspection PhpUnused
 */
class FeatureContext implements Context {
    use CampaignSteps;

    private Driver $driver;
    private Assertion $assert;

    public function __construct() {
        $this->driver = $this->createDriver();
        $this->assert = new Assertion();
        $this->driver->initialize();
    }

    private function createDriver(): Driver {
        return match (\getEnv('TEST_CHANNEL')) {
            'in-memory'   => InMemoryDriver::create(),
            'integration' => new IntegrationDriver(),
            'acceptance'  => new AcceptanceDriver(),
            default       => throw new \Error('Failed to resolve the test channel.'),
        };
    }

    #[AfterScenario]
    public function finalizeDriver(): void {
        $this->driver->finalize();
    }
}
