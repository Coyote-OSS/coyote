<?php
namespace Neon2\Payment\Provider;

use Neon2\Payment\PreparedPayment;

interface PaymentProvider {
    public function prepareCardPayment(int $amount, string $paymentId): PreparedPayment;
}
