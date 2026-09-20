<?php
namespace Features\Dsl\Driver\Channel\AcceptanceChannel;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class CampaignIdMappingTest extends TestCase {
    #[Test]
    public function readsBackTheIdSetForACampaignName(): void {
        $mapping = new CampaignIdMapping();
        $mapping->setCampaignId('sale', 42);
        $this->assertSame(42, $mapping->getCampaignId('sale'));
    }

    #[Test]
    public function readingAnUnknownCampaignName_throws(): void {
        $mapping = new CampaignIdMapping();
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageIs('Campaign has not been assigned an id: unknown');
        $mapping->getCampaignId('unknown');
    }

    #[Test]
    public function overridingAnAlreadySetCampaignName_throws(): void {
        $mapping = new CampaignIdMapping();
        $mapping->setCampaignId('sale', 42);
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageIs('Campaign already has an id assigned: sale');
        $mapping->setCampaignId('sale', 99);
    }
}
