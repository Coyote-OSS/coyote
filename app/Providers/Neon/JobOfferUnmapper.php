<?php
namespace Coyote\Providers\Neon;

use Coyote;
use Coyote\User;
use Neon2\JobBoard\LegalForm;
use Neon2\JobBoard\WorkExperience;
use Neon2\Request\ApplicationMode;
use Neon2\Request\HiringType;
use Neon2\Request\JobOfferLocation;
use Neon2\Request\JobOfferSubmit;

class JobOfferUnmapper {
    public function jobOfferInput(User $owner, JobOfferSubmit $jobOffer): array {
        return [
            'user_id'         => $owner->id,
            'title'           => $jobOffer->title,
            'description'     => $jobOffer->description,
            'salary_from'     => $jobOffer->salaryRangeFrom,
            'salary_to'       => $jobOffer->salaryRangeTo,
            'is_gross'        => !$jobOffer->salaryIsNet,
            'currency_id'     => $this->coyoteCurrencyId($jobOffer->salaryCurrency),
            'rate'            => $jobOffer->salaryRate->value,
            'locations'       => $this->coyoteLocations($jobOffer),
            'is_remote'       => $jobOffer->workModeRemoteRange > 0,
            'remote_range'    => $jobOffer->workModeRemoteRange,
            'employment'      => $this->coyoteLegalForm($jobOffer->legalForm),
            'seniority'       => $this->coyoteWorkExperience($jobOffer->experience),
            'enable_apply'    => $this->coyoteApplicationMode($jobOffer->applicationMode),
            'apply_type'      => $this->coyoteApplicationMode($jobOffer->applicationMode) ? 'service' : 'external',
            'email'           => $jobOffer->applicationEmail,
            'application_url' => $jobOffer->applicationExternalAts,
            'features'        => [],
        ];
    }

    public function companyInput(User $owner, JobOfferSubmit $jobOffer): array {
        return [
            'user_id'     => $owner->id,
            'name'        => $jobOffer->companyName,
            'slug'        => str_slug($jobOffer->companyName),
            'logo'        => $jobOffer->companyLogoUrl,
            'website'     => $jobOffer->companyWebsiteUrl,
            'description' => $jobOffer->companyDescription,
            'youtube_url' => $jobOffer->companyVideoUrl,
            'employees'   => $jobOffer->companySizeLevel,
            'founded'     => $jobOffer->companyFundingYear,
            'is_agency'   => $jobOffer->companyHiringType === HiringType::Agency,
            'benefits'    => [],
            ...$this->coyoteFirmFields($jobOffer),
        ];
    }

    public function planId(string $jobOfferPlan): int {
        return Coyote\Plan::query()
            ->where('is_active', true)
            ->where('name', \ucFirst($jobOfferPlan))
            ->firstOrFail('id')
            ->id;
    }

    private function coyoteCurrencyId(\Neon2\JobBoard\Currency $currency): int {
        return Coyote\Currency::query()
            ->where('name', $currency->value)
            ->firstOrFail('id')
            ->id;
    }

    public function coyoteTagNames(array $tagNames, array $tagPriorities): array {
        return \array_map(
            fn(string $tagName, int $tagPriority) => ['name' => $tagName, 'priority' => $tagPriority],
            $tagNames,
            $tagPriorities);
    }

    private function coyoteLegalForm(\Neon2\JobBoard\LegalForm $legalForm): string {
        return match ($legalForm) {
            LegalForm::EmploymentContract      => 'employment',
            LegalForm::ContractOfMandate       => 'mandatory',
            LegalForm::ContractForSpecificTask => 'contract',
            LegalForm::BusinessToBusiness      => 'b2b',
        };
    }

    private function coyoteWorkExperience(\Neon2\JobBoard\WorkExperience $experience): ?string {
        return match ($experience) {
            WorkExperience::Intern      => 'student',
            WorkExperience::Junior      => 'junior',
            WorkExperience::MidLevel    => 'mid',
            WorkExperience::Senior      => 'senior',
            WorkExperience::Lead        => 'lead',
            WorkExperience::Manager     => 'manager',
            WorkExperience::NotProvided => null,
        };
    }

    private function coyoteApplicationMode(\Neon2\Request\ApplicationMode $applicationMode): bool {
        return $applicationMode === ApplicationMode::_4programmers;
    }

    public function coyoteCompanyPhotoAssets(JobOfferSubmit $jobOffer): array {
        return Coyote\Models\Asset::query()
            ->whereIn('path', \array_map(
                $this->assetPath(...),
                $jobOffer->companyPhotoUrls))
            ->get()
            ->toArray();
    }

    private function assetPath(mixed $photoUrl): string {
        return \explode('/uploads/', $photoUrl)[1];
    }

    private function coyoteLocations(JobOfferSubmit $jobOffer): array {
        return \array_map(
            fn(JobOfferLocation $location) => [
                'city'          => $location->city,
                'latitude'      => $location->latitude,
                'longitude'     => $location->longitude,
                'street'        => $location->streetName,
                'street_number' => $location->streetNumber,
                'country_id'    => $this->coyoteCountryId($location->countryCode),
            ],
            $jobOffer->locations);
    }

    private function coyoteCountryId(?string $countryCode): ?int {
        if ($countryCode === null) {
            return null;
        }
        return Coyote\Country::query()
            ->where('code', $countryCode)
            ->first('id')
            ?->id;
    }

    private function coyoteFirmFields(JobOfferSubmit $jobOffer): array {
        if ($jobOffer->companyAddress === null) {
            return [];
        }
        return [
            'country_id'    => $this->coyoteCountryId($jobOffer->companyAddress->countryCode),
            'city'          => $jobOffer->companyAddress->city,
            'street'        => $jobOffer->companyAddress->streetName,
            'street_number' => $jobOffer->companyAddress->streetNumber,
            'postcode'      => $jobOffer->companyAddress->postalCode,
            'latitude'      => $jobOffer->companyAddress->latitude,
            'longitude'     => $jobOffer->companyAddress->longitude,
        ];
    }
}
