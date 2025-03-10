<?php
namespace Neon\Acceptance\Test;

use Neon\Acceptance\Test\Dsl\Driver;
use PHPUnit\Framework\Assert;
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

    #[Test]
    public function jobOfferIsAdded(): void
    {
        $this->driver->addJobOffer('Java Developer');
        $this->assertJobOffer(expectedJobOfferTitle:'Java Developer');
    }

    private function assertJobOffer(string $expectedJobOfferTitle): void
    {
        Assert::assertContains($expectedJobOfferTitle, $this->driver->fetchJobOffers());
    }
}
