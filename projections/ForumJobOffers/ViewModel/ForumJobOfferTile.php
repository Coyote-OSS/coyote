<?php
namespace Projections\ForumJobOffers\ViewModel;

readonly class ForumJobOfferTile {
    /**
     * @param string[] $headerPills
     * @param Tag[] $technologyTags
     */
    public function __construct(
        public string  $companyName,
        public ?string $companyLogoUrl,
        public string  $jobOfferHref,
        public string  $offerTitle,
        public array   $headerPills,
        public string  $salary,
        public array   $technologyTags,
    ) {}
}
