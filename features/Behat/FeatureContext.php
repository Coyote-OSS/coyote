<?php
namespace Features\Behat;

use Behat\Behat\Context\Context;
use Features\Dsl\Driver\Channel\InMemoryChannel\InMemoryDriver;
use features\Dsl\Driver\Driver;

class FeatureContext implements Context {
    use CampaignSteps;

    private Driver $driver;
    private Assertion $assert;

    public function __construct() {
        $this->driver = new InMemoryDriver();
        $this->assert = new Assertion();
    }
}
