<?php
namespace Test\Neon2\Unit;

use Neon\Currency;
use Neon\LegalForm;
use Neon\Rate;
use Neon\WorkExperience;
use Neon\WorkMode;
use Neon2\JobBoard;
use Neon2\JobBoard\JobOfferStatus;
use Neon2\JobBoard\PaymentIntent;
use Neon2\Payment;
use Neon2\Request\ApplicationMode;
use Neon2\Request\HiringType;
use Neon2\Request\JobOfferSubmit;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class JobBoardTest extends TestCase {
    private JobBoard $jobBoard;
    private JobBoard\JobBoardGate|MockObject $jobBoardGate;

    #[Before]
    public function initialize(): void {
        $this->jobBoardGate = $this->createMock(JobBoard\JobBoardGate::class);
        $this->jobBoard = new JobBoard(
            $this->createMock(Payment\PaymentGate::class),
            $this->jobBoardGate,
            $this->createMock(JobBoard\PlanBundleGate::class),
            testMode:false,
        );
    }

    #[Test]
    public function freeJobOffers_dontContainPaymentIdUnnecessarily(): void {
        $this->expectPaymentId(null);
        $this->jobBoard->createJobOffer('free', $this->jobOffer('Offer'));
    }

    private function expectPaymentId(?string $expectedPaymentId): void {
        $this->jobBoardGate->method('createJobOffer')
            ->willReturnCallback(function (
                JobOfferSubmit $jobOffer,
                string         $pricingPlan,
                int            $expiresInDays,
                JobOfferStatus $status,
                ?PaymentIntent $paymentIntent)
            use ($expectedPaymentId): JobBoard\JobOffer {
                $this->assertSame($expectedPaymentId, $paymentIntent?->paymentId);
                return $this->createMock(JobBoard\JobOffer::class);
            });
    }

    private function jobOffer(string $title): JobOfferSubmit {
        return new JobOfferSubmit(
            $title,
            null,
            null,
            null,
            false,
            Currency::PLN,
            Rate::Monthly,
            [],
            [],
            WorkMode::Hybrid,
            LegalForm::BusinessToBusiness,
            WorkExperience::Intern,
            ApplicationMode::_4programmers,
            null,
            null,
            null,
            null,
            null,
            null,
            [],
            null,
            null,
            null,
            null,
            HiringType::Direct,
        );
    }
}
