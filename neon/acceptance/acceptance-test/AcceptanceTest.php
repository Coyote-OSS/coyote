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
        $this->driver->addJobOffer('Kotlin Developer');
        $this->driver->visitJobOffers();
        Assert::assertContains('Java Developer', $this->driver->fetchJobOffers());
        Assert::assertContains('Kotlin Developer', $this->driver->fetchJobOffers());
    }

    #[Test]
    public function jobOfferIsSortedByPublishDate(): void
    {
        $this->driver->addJobOffer('Java Developer', publishDate:'2010-01-01');
        $this->driver->addJobOffer('Kotlin Developer', publishDate:'2020-02-02');
        $this->driver->visitJobOffers();
        $this->driver->sortJobOffersByDate();
        $this->assertJobOffersOrder(higher:'Kotlin Developer', lower:'Java Developer');
    }

    #[Test]
    public function jobOfferIsSortedByHighestSalary(): void
    {
        $this->driver->addJobOffer('Ruby Developer', salaryTo:1000);
        $this->driver->addJobOffer('Java Developer', salaryTo:1200);
        $this->driver->visitJobOffers();
        $this->driver->sortJobOffersByHighestSalary();
        $this->assertJobOffersOrder(higher:'Java Developer', lower:'Ruby Developer');
    }

    #[Test]
    public function jobOfferIsFilteredBySearchPhrase(): void
    {
        $this->driver->addJobOffer('Ruby Developer', salaryTo:1000);
        $this->driver->addJobOffer('Java Developer', salaryTo:1200);
        $this->driver->visitJobOffers();
        $this->driver->filterJobOffers('Java');
        Assert::assertContains('Java Developer', $this->driver->fetchJobOffers());
        Assert::assertNotContains('Ruby Developer', $this->driver->fetchJobOffers());
    }

    #[Test]
    public function jobOfferIsFilteredByMinimumSalary(): void
    {
        $this->driver->addJobOffer('Python Developer', salaryTo:4500);
        $this->driver->addJobOffer('Kotlin Developer', salaryTo:5500);
        $this->driver->visitJobOffers();
        $this->driver->filterJobOffersBySalary(5000);
        Assert::assertContains('Kotlin Developer', $this->driver->fetchJobOffers());
        Assert::assertNotContains('Python Developer', $this->driver->fetchJobOffers());
    }

    #[Test]
    public function jobOfferIsFilteredByWorkModeFullyRemote(): void
    {
        $this->driver->addJobOffer('Java Developer', workMode:'stationary');
        $this->driver->addJobOffer('Kotlin Developer', workMode:'fullyRemote');
        $this->driver->visitJobOffers();
        $this->driver->filterJobOffersByWorkMode('fullyRemote');
        Assert::assertContains('Kotlin Developer', $this->driver->fetchJobOffers());
        Assert::assertNotContains('Java Developer', $this->driver->fetchJobOffers());
    }

    #[Test]
    public function jobOfferIsFilteredByWorkModeHybrid(): void
    {
        $this->driver->addJobOffer('Java Developer', workMode:'stationary');
        $this->driver->addJobOffer('Kotlin Developer', workMode:'hybrid');
        $this->driver->visitJobOffers();
        $this->driver->filterJobOffersByWorkMode('hybrid');
        Assert::assertContains('Kotlin Developer', $this->driver->fetchJobOffers());
        Assert::assertNotContains('Java Developer', $this->driver->fetchJobOffers());
    }

    #[Test]
    public function jobOfferIsFilteredByLocation(): void
    {
        $this->driver->addJobOffer('PHP Developer', location:'New York');
        $this->driver->addJobOffer('Python Developer', location:'London');
        $this->driver->visitJobOffers();
        $this->driver->filterJobOffersByLocation('London');
        Assert::assertContains('Python Developer', $this->driver->fetchJobOffers());
        Assert::assertNotContains('PHP Developer', $this->driver->fetchJobOffers());
    }

    #[Test]
    public function jobOffersCanBeCleared(): void
    {
        $this->driver->addJobOffer('Job Offer');
        $this->driver->visitJobOffers();
        $this->driver->filterJobOffers('Missing');
        $this->driver->clearFilters();
        Assert::assertContains('Job Offer', $this->driver->fetchJobOffers());
    }

    private function assertJobOffersOrder(string $higher, string $lower): void
    {
        Assert::assertEquals(
            1,
            $this->driver->jobOffersOrder($higher, $lower),
            'Failed to assert order of job offers.');
    }
}
