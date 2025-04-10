<?php
namespace Neon2\Payment\Provider;

use Neon2\Payment\PaymentStatus;
use Neon2\Payment\PaymentUpdate;
use Stripe as StripeApi;
use Stripe\Exception\SignatureVerificationException;

readonly class StripeWebhook implements PaymentWebhook {
    public function __construct(private string $signingPrivateKey) {}

    public function acceptPaymentUpdate(string $payload, string $signature): ?PaymentUpdate {
        try {
            return $this->parse($this->webhookEvent($payload, $signature));
        } catch (StripeApi\Exception\SignatureVerificationException) {
            return null;
        }
    }

    private function parse(StripeApi\Event $event): ?PaymentUpdate {
        if ($event->type === 'payment_intent.succeeded') {
            return new PaymentUpdate(PaymentStatus::Completed, $this->paymentId($event));
        }
        if ($event->type === 'payment_intent.payment_failed') {
            return new PaymentUpdate(PaymentStatus::Failed, $this->paymentId($event));
        }
        return null;
    }

    private function paymentId(StripeApi\Event $event): string {
        /** @var StripeApi\PaymentIntent $object */
        $object = $event->data->object;
        return $object->metadata['paymentId'];
    }

    /**
     * @throws SignatureVerificationException
     */
    private function webhookEvent(string $payload, string $signature): StripeApi\Event {
        return StripeApi\Webhook::constructEvent($payload, $signature, $this->signingPrivateKey);
    }
}
