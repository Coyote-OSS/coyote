<?php
namespace Neon2\Payment\Provider;

use Neon2\Payment\PaymentUpdate;

interface PaymentWebhook {
    public function acceptPaymentUpdate(string $payload, string $signature): ?PaymentUpdate;
}
