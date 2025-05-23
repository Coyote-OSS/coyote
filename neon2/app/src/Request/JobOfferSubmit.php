<?php
namespace Neon2\Request;

use Neon2\JobBoard\Currency;
use Neon2\JobBoard\LegalForm;
use Neon2\JobBoard\Rate;
use Neon2\JobBoard\WorkExperience;

readonly class JobOfferSubmit {
    /**
     * @param JobOfferLocation[] $locations
     * @param string[] $tagNames
     * @param int[] $tagPriorities
     * @param string[] $companyPhotoUrls
     */
    public function __construct(
        public string            $title,
        public ?string           $description,
        public ?int              $salaryRangeFrom,
        public ?int              $salaryRangeTo,
        public bool              $salaryIsNet,
        public Currency          $salaryCurrency,
        public Rate              $salaryRate,
        public array             $locations,
        public array             $tagNames,
        public array             $tagPriorities,
        public int               $workModeRemoteRange,
        public LegalForm         $legalForm,
        public WorkExperience    $experience,
        public ApplicationMode   $applicationMode,
        public ?string           $applicationEmail,
        public ?string           $applicationExternalAts,
        public ?string           $companyName,
        public ?string           $companyLogoUrl,
        public ?string           $companyWebsiteUrl,
        public ?string           $companyDescription,
        public array             $companyPhotoUrls,
        public ?string           $companyVideoUrl,
        public ?int              $companySizeLevel,
        public ?int              $companyFundingYear,
        public ?JobOfferLocation $companyAddress,
        public HiringType        $companyHiringType,
    ) {}
}
