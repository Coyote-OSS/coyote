<?php
namespace Neon\Acceptance\Test;

use Neon\Acceptance\Test\Dsl\Driver;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class AcceptanceTest extends TestCase
{
    private Driver $driver;

    #[Before]
    public function initialize(): void
    {
        $this->driver = new Driver();
    }

    #[After]
    public function close(): void
    {
        if ($this->status()->isFailure() || $this->status()->isError()) {
            $this->driver->includeDiagnosticArtifact($this->name());
        }
        $this->driver->close();
    }

    #[Test]
    public function jobOfferIsAdded(): void
    {
        $this->driver->addJobOffer('Java Developer');
        $this->assertJobOfferExists(expectedJobOfferTitle:'Java Developer');
    }

    #[Test]
    public function jobOfferIsLimitedBySearchPhrase(): void
    {
        $this->driver->addJobOffer('Java Developer');
        $this->driver->addJobOffer('Kotlin Developer');
        $this->driver->search('Kotlin');
        $this->assertJobOfferExists(expectedJobOfferTitle:'Kotlin Developer');
        $this->assertJobOfferNotExists(expectedJobOfferTitle:'Java Developer');
    }

    private function assertJobOfferExists(string $expectedJobOfferTitle): void
    {
        Assert::assertContains($expectedJobOfferTitle, $this->driver->fetchJobOffers());
    }

    private function assertJobOfferNotExists(string $expectedJobOfferTitle): void
    {
        Assert::assertNotContains($expectedJobOfferTitle, $this->driver->fetchJobOffers());
    }
}
