<?php
namespace Features\Behat;

use Behat\Behat\Context\Context;
use Behat\Hook\AfterScenario;
use Features\Dsl\Driver\Channel\InMemoryChannel\InMemoryDriver;
use Features\Dsl\Driver\Channel\IntegrationChannel\IntegrationDriver;
use Features\Dsl\Driver\Driver;
use Test\Modules\Campaigns\Store\InMemoryCampaignsStore;

/**
 * @noinspection PhpUnused
 */
class FeatureContext implements Context {
    use CampaignSteps;

    private Driver $driver;
    private Assertion $assert;

    public function __construct() {
        $this->driver = $this->initializeDriver();
        $this->assert = new Assertion();
    }

    private function initializeDriver(): Driver {
        return match (\getEnv('TEST_CHANNEL')) {
            'in-memory'   => new InMemoryDriver(new InMemoryCampaignsStore()),
            'integration' => new IntegrationDriver(),
            default       => throw new \Error('Failed to resolve the test channel.'),
        };
    }

    #[AfterScenario]
    public function closeDriver(): void {
        $this->driver->close();
    }
}
