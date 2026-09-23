<?php
namespace Features\Behat;

use Behat\Behat\Tester\Exception\PendingException;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;

trait JobOfferSteps {
    #[Given('there is a job offer :jobOffer')]
    public function thereIsAJobOffer(string $jobOffer): void {
        throw new PendingException();
    }

    #[Then('the job offer :jobOffer has :clicks clicks')]
    public function theJobOfferHasClicks(string $jobOffer, int $clicks): void {
        throw new PendingException();
    }

    #[When('a user clicks the tile of the job offer :jobOffer twice')]
    public function aUserClicksTheTileOfTheJobOfferTwice(string $jobOFfer): void {
        throw new PendingException();
    }
}
