<?php
namespace Features\Dsl\Driver\Channel\AcceptanceChannel;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class JobOfferIdMappingTest extends TestCase {
    #[Test]
    public function readsBackTheIdSetForAJobOfferName(): void {
        $mapping = new JobOfferIdMapping();
        $mapping->setJobOfferId('php-developer', 42);
        $this->assertSame(42, $mapping->getJobOfferId('php-developer'));
    }

    #[Test]
    public function readingAnUnknownJobOfferName_throws(): void {
        $mapping = new JobOfferIdMapping();
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageIs('Job offer has not been assigned an id: unknown');
        $mapping->getJobOfferId('unknown');
    }

    #[Test]
    public function overridingAnAlreadySetJobOfferName_throws(): void {
        $mapping = new JobOfferIdMapping();
        $mapping->setJobOfferId('php-developer', 42);
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageIs('Job offer already has an id assigned: php-developer');
        $mapping->setJobOfferId('php-developer', 99);
    }
}
