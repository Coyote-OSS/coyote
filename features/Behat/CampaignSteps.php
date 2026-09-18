<?php
namespace Features\Behat;

use Behat\Gherkin\Node\TableNode;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;
use function array_shift;

trait CampaignSteps {
    /** @var array<int, array<string, string[]>> */
    private array $renderedSlots = [];

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

    #[When('variants are resolved :times times for a user on :deviceType')]
    public function variantsAreResolvedForAUserOnDeviceMultipleTimes(string $deviceType, int $times): void {
        $this->renderedSlots = [];
        for ($i = 0; $i < $times; $i++) {
            $this->driver->resolveVariantsForUser($deviceType);
            $this->renderedSlots[] = [
                'header' => $this->driver->variantsForSlot('header'),
                'feed'   => $this->driver->variantsForSlot('feed'),
                'square' => $this->driver->variantsForSlot('square'),
            ];
        }
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

    #[Then('the :slotType slot rotates through:')]
    public function theSlotRotatesThrough(string $slotType, TableNode $table): void {
        $this->assert->assertEquals(
            $table->getRows(),
            \array_column($this->renderedSlots, $slotType));
    }

    #[Then('the following slots contain:')]
    public function theFollowingSlotsContain(TableNode $expectedSlots): void {
        foreach ($expectedSlots->getRows() as $slot) {
            $slotType = array_shift($slot);
            $this->assert->assertEquals($slot, $this->driver->variantsForSlot($slotType));
        }
    }

    #[Given('there is a campaign :campaign, which has a :variantType variant :variantUrl')]
    public function thereIsACampaignWhichHasAVariant(string $campaign, string $variantType, string $variantUrl): void {
        $this->driver->createCampaign($campaign, premium:false);
        $this->driver->addVariant($campaign, $variantType, $variantUrl);
    }
}
