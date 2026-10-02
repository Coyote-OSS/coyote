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

    #[Given('a user opened a page with the tile of the job offer :jobOffer below the viewport')]
    #[When('a user opens a page with the tile of the job offer :jobOffer below the viewport')]
    public function aUserOpensAPageWithTheTileOfTheJobOfferBelowTheViewport(string $jobOffer): void {
        $this->driver->renderJobOfferTile($jobOffer, insideViewport:false);
    }

    #[When('the user scrolls to the tile of the job offer :jobOffer and sees it for a second')]
    public function theUserScrollsToTheTileOfTheJobOfferAndSeesItForASecond(string $jobOffer): void {
        $this->driver->renderJobOfferTile($jobOffer, insideViewport:true);
    }

    #[Then('the job offer :jobOffer has :exposures exposure')]
    #[Then('the job offer :jobOffer has :exposures exposures')]
    public function theJobOfferHasExposures(string $jobOffer, int $exposures): void {
        $this->assert->assertEquals(
            $exposures,
            $this->driver->readJobOfferExposures($jobOffer));
    }
}
