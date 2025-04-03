<?php
namespace Neon2;

class JobOffer
{
    public function __construct(
        public int            $id,
        public string         $title,
        public int            $expiresInDays,
        public JobOfferStatus $status,
    ) {}
}
