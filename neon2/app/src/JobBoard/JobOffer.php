<?php
namespace Neon2\JobBoard;

use Neon2\Request\JobOfferSubmit;

class JobOffer {
    public function __construct(
        public int            $id,
        public int            $expiresInDays,
        public JobOfferStatus $status,
        public ?string        $paymentId,
        public JobOfferSubmit $fields,
    ) {}
}
