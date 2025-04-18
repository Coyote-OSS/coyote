<?php
namespace Test\Neon2\Unit;

use Neon2\JobBoard;
use Neon2\Payment;
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
        $this->jobBoard->createJobOffer('free', new JobOfferSubmit('Offer', '', ''));
    }

    private function expectPaymentId(?string $expectedPaymentId): void {
        $this->jobBoardGate->method('createJobOffer')
            ->willReturnCallback(function (JobOfferSubmit $jobOffer, string $pricingPlan, ?string $paymentId)
            use ($expectedPaymentId): JobBoard\JobOffer {
                $this->assertSame($expectedPaymentId, $paymentId);
                return $this->createMock(JobBoard\JobOffer::class);
            });
    }
}
