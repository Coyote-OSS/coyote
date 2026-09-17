<?php
namespace Features\Behat;

use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;

trait CampaignSteps {
    #[Given('there is a campaign :campaign')]
    #[Given('there is a standard campaign :campaign')]
    #[Given('there is a sole campaign :campaign')]
    public function thereIsACampaign(string $campaign): void {
        $this->driver->createCampaign($campaign, premium:false);
    }

    #[Given('there is a premium campaign :campaign')]
    public function thereIsAPremiumCampaign(string $campaign): void {
        $this->driver->createCampaign($campaign, premium:true);
    }

    #[Given('the campaign :campaign has a :variantType variant :variantUrl')]
    #[Given('the campaign :campaign also has a :variantType variant :variantUrl')]
    public function theCampaignHasAVariant(string $campaign, string $variantType, string $variantUrl): void {
        $this->driver->addVariant($campaign, $variantType, $variantUrl);
    }

    #[Given('it is not a sole campaign')]
    public function itIsNotASoleCampaign(): void {
        $this->driver->createCampaign('another campaign', premium:false);
    }

    #[When('variants are resolved for a user on :deviceType')]
    #[When('variants are resolved for a user on :deviceType again')]
    public function variantsAreResolvedForAUserOnDevice(string $deviceType): void {
        $this->driver->resolveVariantsForUser($deviceType);
    }

    #[When('variants are resolved')]
    public function variantsAreResolved(): void {
        $this->driver->resolveVariantsForUser('desktop');
    }

    #[Then('the :slotType slot contains :variantUrl')]
    public function theSlotContainsVariant(string $slotType, string $variantUrl): void {
        $this->assert->assertContains($variantUrl, $this->driver->variantsForSlot($slotType));
    }

    #[Then('the :slotType slot does not contain :variantUrl')]
    public function theSlotDoesNotContainVariant(string $slotType, string $variantUrl): void {
        $this->assert->assertNotContains($variantUrl, $this->driver->variantsForSlot($slotType));
    }

    #[Then('the :slotType slot is empty')]
    public function theSlotIsEmpty(string $slotType): void {
        $this->assert->assertEmpty($this->driver->variantsForSlot($slotType));
    }

    #[Given('there is a campaign :campaign, which has a :variantType variant :variantUrl')]
    public function thereIsACampaignWhichHasAVariant(string $campaign, string $variantType, string $variantUrl): void {
        $this->driver->createCampaign($campaign, premium:false);
        $this->driver->addVariant($campaign, $variantType, $variantUrl);
    }
}
