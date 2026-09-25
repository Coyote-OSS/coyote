<?php
namespace Web\Projections\ForumJobOffers\ViewModel;

readonly class ForumJobOfferTile {
    /**
     * @param string[] $headerPills
     * @param Tag[] $technologyTags
     */
    public function __construct(
        public string  $companyName,
        public ?string $companyLogoUrl,
        public string  $jobOfferHref,
        public string  $jobOfferClickHref,
        public string  $jobOfferExposureHref,
        public string  $jobOfferTitle,
        public array   $headerPills,
        public string  $salaryFormat,
        public bool    $salaryDisclosed,
        public bool    $isNew,
        public array   $technologyTags,
    ) {}
}
