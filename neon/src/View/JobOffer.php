<?php
namespace Neon\View;

/**
 * @property string[] $locations
 * @property string[] $tagNames
 */
readonly class JobOffer
{
    public function __construct(
        public string     $title,
        public string     $url,
        public array      $locations,
        public WorkMode   $workMode,
        public bool       $isFavourite,
        public bool       $isNew,
        public string     $publishDate,
        public array      $tagNames,
        public ?string    $companyName,
        public ?string    $companyLogoUrl,
        public int        $commentsCount,
        public ?int       $salaryFrom,
        public ?int       $salaryTo,
        public ?Currency  $salaryCurrency,
        public bool       $salaryIncludesTax,
        public Settlement $salarySettlement,
    ) {}
}
