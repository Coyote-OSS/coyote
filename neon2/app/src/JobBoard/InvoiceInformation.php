<?php
namespace Neon2\JobBoard;

readonly class InvoiceInformation {
    public function __construct(
        public string $vatId,
        public string $countryCode,
        public string $companyName,
        public string $companyAddress,
        public string $companyPostalCode,
        public string $companyCity,
    ) {}
}
