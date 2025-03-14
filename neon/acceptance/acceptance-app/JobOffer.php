<?php
namespace Neon\Acceptance;

use Neon\WorkMode;

readonly class JobOffer
{
    public function __construct(
        public string   $title,
        public string   $url,
        public string   $publishDate,
        public int      $salaryTo,
        public WorkMode $workMode,
        public array    $locations,
        public ?string  $companyName,
    ) {}
}
