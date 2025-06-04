<?php
namespace Neon2\Coyote;

use Neon2\JobBoard\InvoiceInformation;
use Neon2\JobBoard\JobOffer;
use Neon2\JobBoard\PlanBundle;
use Neon2\Payment\PaymentStatus;
use Neon2\Payment\PreparedPayment;
use Neon2\Request\JobOfferSubmit;

interface Integration {
    /**
     * @return JobOffer[]
     */
    public function listJobOffers(?int $includeExpired): array;

    public function planBundle(?int $userId): PlanBundle;

    public function redeemBundle(int $userId, int $jobOfferId): void;

    public function revokePlanBundle(int $userId): void;

    public function createJobOffer(
        string         $jobOfferPlan,
        JobOfferSubmit $jobOffer,
        bool           $acceptanceTestExpired,
        bool           $shortExpiryDate,
    ): JobOffer;

    public function updateJobOffer(int $jobOfferId, JobOfferSubmit $jobOffer): void;

    public function preparePayment(int $userId, string $paymentId, InvoiceInformation $invoiceInfo): PreparedPayment;

    public function paymentWebhook(string $webhookPayload, string $stripeSignature): void;

    public function paymentStatus(string $paymentId): PaymentStatus;
}
