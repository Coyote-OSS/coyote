<?php
namespace Neon2\Payment\Provider;

use Neon2\Payment\PaymentStatus;
use Neon2\Payment\PaymentUpdate;

readonly class TestPaymentWebhook implements PaymentWebhook {
    public function __construct(private TestPaymentProvider $provider) {}

    public function acceptPaymentUpdate(string $payload, string $signature): ?PaymentUpdate {
        return $this->paymentUpdate($this->payload($payload));
    }

    private function paymentUpdate(array $payload): PaymentUpdate {
        return new PaymentUpdate(
            $this->status($payload['paymentStatus']),
            $this->paymentId($payload['paymentToken']));
    }

    private function status(string $status): PaymentStatus {
        return match ($status) {
            'completed' => PaymentStatus::Completed,
            'failed'    => PaymentStatus::Failed,
            default     => PaymentStatus::Awaiting,
        };
    }

    private function paymentId(string $paymentToken): string {
        return $this->provider->paymentIdByPaymentToken($paymentToken);
    }

    private function payload(string $payload): array {
        return \json_decode($payload, true, flags:\JSON_THROW_ON_ERROR);
    }
}
