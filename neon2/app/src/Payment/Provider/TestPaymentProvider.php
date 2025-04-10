<?php
namespace Neon2\Payment\Provider;

use Neon2\Payment\PreparedPayment;

readonly class TestPaymentProvider implements PaymentProvider {
    private TestPaymentGate $payments;

    public function __construct() {
        $this->payments = new TestPaymentGate();
    }

    public function prepareCardPayment(int $amount, string $paymentId): PreparedPayment {
        $paymentToken = $this->generatePaymentToken();
        $this->payments->addPaymentId($paymentToken, $paymentId);
        return new PreparedPayment(true, $paymentId, $paymentToken);
    }

    private function generatePaymentToken(): string {
        return 'test-payment-token-' . \uniqId();
    }

    public function paymentIdByPaymentToken(string $paymentToken): string {
        return $this->payments->fetchPaymentId($paymentToken);
    }
}
