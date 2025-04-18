<?php
namespace Test\Neon2\Integration;

use Neon\Currency;
use Neon\LegalForm;
use Neon\Rate;
use Neon\WorkExperience;
use Neon\WorkMode;
use Neon2\JobBoard\JobBoardGate;
use Neon2\JobBoard\JobOffer;
use Neon2\JobBoard\JobOfferStatus;
use Neon2\Request\JobOfferSubmit;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class JobBoardGateTest extends TestCase {
    private JobBoardGate $board;

    #[Before]
    public function initialize(): void {
        $this->board = new JobBoardGate();
        $this->board->clear();
    }

    #[Test]
    public function emptyOffers(): void {
        $this->assertEquals([], $this->board->listJobOffers());
    }

    #[Test]
    public function addJobOffer(): void {
        $this->addFreeOffer('Foo');
        [$jobOffer] = $this->board->listJobOffers();
        $this->assertEquals('Foo', $jobOffer->fields->title);
    }

    #[Test]
    public function newIdIsGeneratedForNewJob(): void {
        $this->addFreeOffer('Foo');
        $this->addFreeOffer('Bar');
        [$jobOffer1, $jobOffer2] = $this->board->listJobOffers();
        $this->assertNotEquals($jobOffer1->id, $jobOffer2->id);
    }

    #[Test]
    public function freeOfferExpiresIn14Days(): void {
        $this->addFreeOffer('Foo');
        [$jobOffer] = $this->board->listJobOffers();
        $this->assertSame(14, $jobOffer->expiresInDays);
    }

    #[Test]
    public function paidOfferExpiresIn30Days(): void {
        $this->addPaidOffer('Foo');
        [$jobOffer] = $this->board->listJobOffers();
        $this->assertSame(30, $jobOffer->expiresInDays);
    }

    #[Test]
    public function editJobOffer(): void {
        $jobOffer = $this->board->createJobOffer($this->fields('Foo'), 'free', '');
        $this->board->updateJobOffer($jobOffer->id, $this->fields('New title'));
        [$jobOffer] = $this->board->listJobOffers();
        $this->assertEquals('New title', $jobOffer->fields->title);
    }

    #[Test]
    public function freeJobOfferIsInitiallyPublished(): void {
        $this->addFreeOffer('Foo');
        [$jobOffer] = $this->board->listJobOffers();
        $this->assertEquals(JobOfferStatus::Published, $jobOffer->status);
    }

    #[Test]
    public function paidJobOfferIsInitiallyAwaitingPayment(): void {
        $this->addPaidOffer('Foo');
        [$jobOffer] = $this->board->listJobOffers();
        $this->assertEquals(JobOfferStatus::AwaitingPayment, $jobOffer->status);
    }

    #[Test]
    public function payingPaymentOnPaidJobOfferChangesStatusToPublished(): void {
        $offer = $this->addPaidOffer('Foo');
        $this->board->publishJobOffer($offer->id);
        [$jobOffer] = $this->board->listJobOffers();
        $this->assertEquals(JobOfferStatus::Published, $jobOffer->status);
    }

    #[Test]
    public function readJobOfferIdByPaymentId(): void {
        $jobOffer = $this->board->createJobOffer($this->fields('Foo'), 'free', 'payment-id');
        $this->assertEquals(
            $jobOffer->id,
            $this->board->jobOfferIdByPaymentId('payment-id'));
    }

    #[Test]
    public function readPricingPlanByPaymentId(): void {
        $this->board->createJobOffer($this->fields('Foo'), 'scale', 'payment-id-123');
        $this->assertSame('scale', $this->board->pricingPlanByPaymentId('payment-id-123'));
    }

    private function addFreeOffer(string $title): void {
        $this->board->createJobOffer($this->fields($title), 'free', '');
    }

    private function addPaidOffer(string $title): JobOffer {
        return $this->board->createJobOffer($this->fields($title), 'premium', '');
    }

    private function fields(string $title): JobOfferSubmit {
        return new JobOfferSubmit(
            $title,
            '',
            '',
            0,
            0,
            '',
            Currency::PLN,
            Rate::Monthly,
            [],
            '',
            [],
            WorkMode::Hybrid,
            LegalForm::BusinessToBusiness,
            WorkExperience::Intern,
        );
    }
}
