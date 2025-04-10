<?php
namespace Neon2;

use Neon2\JobBoard\JobBoardGate;
use Neon2\Payment\PaymentGate;
use Neon2\Payment\PaymentStatus;

readonly class JobBoard {
    public function __construct(
        private PaymentGate  $payments,
        private JobBoardGate $jobBoard,
    ) {}

    public function paymentUpdate(Payment\PaymentUpdate $paymentUpdate): void {
        if ($paymentUpdate->type === PaymentStatus::Completed) {
            $this->payments->storePaymentStatus($paymentUpdate->paymentId, PaymentStatus::Completed);
            $this->jobBoard->payJobOfferPayment($paymentUpdate->paymentId);
        }
        if ($paymentUpdate->type === PaymentStatus::Failed) {
            $this->payments->storePaymentStatus($paymentUpdate->paymentId, PaymentStatus::Failed);
        }
    }
}
