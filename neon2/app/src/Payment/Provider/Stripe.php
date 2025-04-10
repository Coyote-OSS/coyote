<?php
namespace Neon2\Payment\Provider;

use Neon2\Payment\PreparedPayment;
use Stripe as StripeApi;

class Stripe implements PaymentProvider {
    private StripeApi\StripeClient $client;

    public function __construct(string $privateKey) {
        $this->client = new StripeApi\StripeClient($privateKey);
    }

    public function prepareCardPayment(int $amount, string $paymentId): PreparedPayment {
        try {
            $intent = $this->createPaymentIntent($amount, ['paymentId' => $paymentId]);
            return new PreparedPayment(true, $paymentId, $intent->client_secret);
        } catch (StripeApi\Exception\ApiErrorException) {
            return new PreparedPayment(false, $paymentId, null);
        }
    }

    /**
     * @throws StripeApi\Exception\ApiErrorException
     */
    private function createPaymentIntent(int $amount, array $metadata): StripeApi\PaymentIntent {
        return $this->client->paymentIntents->create([
            'amount'               => $amount,
            'currency'             => 'pln',
            'metadata'             => $metadata,
            'payment_method_types' => ['card'], // or p24
        ]);
    }
}
