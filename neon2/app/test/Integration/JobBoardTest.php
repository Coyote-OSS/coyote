<?php
namespace Test\Neon2\Integration;

use Neon2\JobBoard\JobBoard;
use Neon2\JobBoard\JobOfferStatus;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class JobBoardTest extends TestCase
{
    private JobBoard $board;

    #[Before]
    public function initialize(): void
    {
        $this->board = new JobBoard();
        $this->board->clear();
    }

    #[Test]
    public function emptyOffers(): void
    {
        $this->assertEquals([], $this->board->listJobOffers());
    }

    #[Test]
    public function addJobOffer(): void
    {
        $this->board->addJobOffer('Foo', 'free');
        [$jobOffer] = $this->board->listJobOffers();
        $this->assertEquals('Foo', $jobOffer->title);
    }

    #[Test]
    public function newIdIsGeneratedForNewJob(): void
    {
        $this->board->addJobOffer('Foo', 'free');
        $this->board->addJobOffer('Bar', 'free');
        [$jobOffer1, $jobOffer2] = $this->board->listJobOffers();
        $this->assertNotEquals($jobOffer1->id, $jobOffer2->id);
    }

    #[Test]
    public function freeOfferExpiresIn14Days(): void
    {
        $this->board->addJobOffer('Foo', 'free');
        [$jobOffer] = $this->board->listJobOffers();
        $this->assertSame(14, $jobOffer->expiresInDays);
    }

    #[Test]
    public function paidOfferExpiresIn30Days(): void
    {
        $this->board->addJobOffer('Foo', 'paid');
        [$jobOffer] = $this->board->listJobOffers();
        $this->assertSame(30, $jobOffer->expiresInDays);
    }

    #[Test]
    public function editJobOffer(): void
    {
        $jobOffer = $this->board->addJobOffer('Foo', 'free');
        $this->board->editJobOffer($jobOffer->id, 'New title');
        [$jobOffer] = $this->board->listJobOffers();
        $this->assertEquals('New title', $jobOffer->title);
    }

    #[Test]
    public function freeJobOfferIsInitiallyPublished(): void
    {
        $this->board->addJobOffer('Foo', 'free');
        [$jobOffer] = $this->board->listJobOffers();
        $this->assertEquals(JobOfferStatus::Published, $jobOffer->status);
    }

    #[Test]
    public function paidJobOfferIsInitiallyAwaitingPayment(): void
    {
        $this->board->addJobOffer('Foo', 'paid');
        [$jobOffer] = $this->board->listJobOffers();
        $this->assertEquals(JobOfferStatus::AwaitingPayment, $jobOffer->status);
    }

    #[Test]
    public function initiatingPaymentOnPaidJobOfferChangesStatusToPublished(): void
    {
        $offer = $this->board->addJobOffer('Foo', 'paid');
        $this->board->initiateJobOfferPayment($offer->id);
        [$jobOffer] = $this->board->listJobOffers();
        $this->assertEquals(JobOfferStatus::Published, $jobOffer->status);
    }
}
