<?php
namespace Neon2;

use Neon2\JobBoard\JobBoardGate;
use Neon2\JobBoard\JobOffer;
use Neon2\Payment\PaymentGate;
use Neon2\Payment\PaymentStatus;

readonly class JobBoard {
    public function __construct(
        private PaymentGate  $payments,
        private JobBoardGate $jobBoard,
        private bool         $testMode,
    ) {}

    public function testMode(): bool {
        return $this->testMode;
    }

    public function paymentUpdate(Payment\PaymentUpdate $paymentUpdate): void {
        if ($paymentUpdate->type === PaymentStatus::Completed) {
            $this->payments->storePaymentStatus($paymentUpdate->paymentId, PaymentStatus::Completed);
            $this->jobBoard->payJobOfferPayment($this->jobOfferId($paymentUpdate->paymentId));
        }
        if ($paymentUpdate->type === PaymentStatus::Failed) {
            $this->payments->storePaymentStatus($paymentUpdate->paymentId, PaymentStatus::Failed);
        }
    }

    public function addJobOffer(string $jobOfferTitle, string $jobOfferPlan): JobOffer {
        return $this->jobBoard->addJobOffer(
            $jobOfferTitle,
            $jobOfferPlan,
            $this->generatePaymentId());
    }

    private function generatePaymentId(): string {
        return \uniqId();
    }

    private function jobOfferId(string $paymentId): int {
        return $this->jobBoard->jobOfferIdByPaymentId($paymentId);
    }
}
