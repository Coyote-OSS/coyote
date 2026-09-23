<?php
namespace Features\Behat;

use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;

trait JobOfferSteps {
    #[Given('there is a job offer :jobOffer')]
    public function thereIsAJobOffer(string $jobOffer): void {
        $this->driver->createJobOffer($jobOffer);
    }

    #[When('a user clicks the tile of the job offer :jobOffer twice')]
    public function aUserClicksTheTileOfTheJobOfferTwice(string $jobOffer): void {
        $this->driver->clickJobOffer($jobOffer);
        $this->driver->clickJobOffer($jobOffer);
    }

    #[Then('the job offer :jobOffer has :clicks clicks')]
    public function theJobOfferHasClicks(string $jobOffer, int $clicks): void {
        $actualClicks = $this->driver->jobOfferClicks($jobOffer);
        $this->assert->assertEquals($clicks, $actualClicks);
    }
}
