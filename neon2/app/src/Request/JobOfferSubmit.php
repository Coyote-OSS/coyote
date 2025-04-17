<?php
namespace Neon2\Request;

readonly class JobOfferSubmit {
    public function __construct(
        public string $title,
        public string $description,
        public string $companyName,
    ) {}
}
