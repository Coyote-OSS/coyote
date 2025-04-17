<?php
namespace Neon2\JobBoard;

class JobOffer {
    public function __construct(
        public int            $id,
        public string         $title,
        public string         $description,
        public string         $companyName,
        public int            $expiresInDays,
        public JobOfferStatus $status,
        public ?string        $paymentId,
    ) {}
}
