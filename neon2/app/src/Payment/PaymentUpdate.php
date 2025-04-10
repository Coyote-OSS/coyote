<?php
namespace Neon2\Payment;

readonly class PaymentUpdate {
    public function __construct(
        public PaymentStatus $type,
        public string        $paymentId,
    ) {}
}
