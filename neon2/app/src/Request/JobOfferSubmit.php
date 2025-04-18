<?php
namespace Neon2\Request;

use Neon\Currency;
use Neon\LegalForm;
use Neon\Rate;
use Neon\WorkExperience;
use Neon\WorkMode;

readonly class JobOfferSubmit {
    public function __construct(
        public string         $title,
        public string         $description,
        public string         $companyName,
        public int            $salaryRangeFrom,
        public int            $salaryRangeTo,
        public bool           $salaryIsNet,
        public Currency       $salaryCurrency,
        public Rate           $salaryRate,
        public array          $locations,
        public string         $companyLogoUrl,
        public array          $tagNames,
        public WorkMode       $workMode,
        public LegalForm      $legalForm,
        public WorkExperience $experience,
    ) {}
}
