<?php
namespace Neon2\Payment;

readonly class PreparedPayment {
    public function __construct(
        public bool    $providerReady,
        public string  $paymentId,
        public ?string $paymentToken,
    ) {}
}
