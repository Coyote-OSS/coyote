<?php
namespace Coyote\Providers\Neon;

use Coyote;
use Coyote\Job;
use Neon2\JobBoard\Currency;
use Neon2\JobBoard\LegalForm;
use Neon2\JobBoard\Rate;
use Neon2\JobBoard\WorkExperience;
use Neon2\Request\ApplicationMode;
use Neon2\Request\HiringType;
use Neon2\Request\JobOfferLocation;
use Neon2\Request\JobOfferSubmit;

readonly class JobOfferMapper {
    public function jobOfferFields(Coyote\Job $jobOffer): JobOfferSubmit {
        return new JobOfferSubmit(
            $jobOffer->title,
            $this->stripFormatting($jobOffer->description),
            $jobOffer->salary_from,
            $jobOffer->salary_to,
            !$jobOffer->is_gross,
            Currency::from($jobOffer->currency->name),
            Rate::from($jobOffer->rate),
            $this->neonLocations($jobOffer),
            $this->neonTagNames($jobOffer),
            $this->neonTagPriorities($jobOffer),
            $this->neonWorkModeRemoteRange($jobOffer),
            $this->neonLegalForm($jobOffer),
            $this->neonWorkExperience($jobOffer),
            $jobOffer->enable_apply ? ApplicationMode::_4programmers : ApplicationMode::ExternalAts,
            $jobOffer->email,
            $jobOffer->application_url,
            $jobOffer->firm->name,
            $this->companyLogoUrl($jobOffer),
            $jobOffer->firm->website,
            $this->stripFormatting($jobOffer->firm->description),
            $this->companyPhotoUrls($jobOffer->firm),
            $jobOffer->firm->youtube_url,
            $jobOffer->firm->employees,
            $jobOffer->firm->founded,
            $this->neonCompanyAddress($jobOffer->firm),
            $jobOffer->firm->is_agency ? HiringType::Agency : HiringType::Direct,
        );
    }

    private function stripFormatting(?string $text): ?string {
        if ($text === null) {
            return null;
        }
        $formattedText = new FormattedText();
        return $formattedText->stripFormatting($text);
    }

    private function companyLogoUrl(Coyote\Job $jobOffer): ?string {
        $logo = $jobOffer->firm->logo;
        if ($logo->getFilename() && $logo->size() > 0) {
            return (string)$jobOffer->firm->logo->url();
        }
        return null;
    }

    private function companyPhotoUrls(Coyote\Firm $firm): array {
        return $firm->assets
            ->map(fn(Coyote\Models\Asset $asset): string => Coyote\Services\Assets\Url::make($asset))
            ->toArray();
    }

    private function neonWorkExperience(Coyote\Job $jobOffer): ?WorkExperience {
        return match ($jobOffer->seniority) {
            'student' => WorkExperience::Intern,
            'junior'  => WorkExperience::Junior,
            'mid'     => WorkExperience::MidLevel,
            'senior'  => WorkExperience::Senior,
            'lead'    => WorkExperience::Lead,
            'manager' => WorkExperience::Manager,
            null      => WorkExperience::NotProvided,
        };
    }

    private function neonLegalForm(Coyote\Job $jobOffer): LegalForm {
        return match ($jobOffer->employment) {
            'employment' => LegalForm::EmploymentContract,
            'mandatory'  => LegalForm::ContractOfMandate,
            'contract'   => LegalForm::ContractForSpecificTask,
            'b2b'        => LegalForm::BusinessToBusiness,
        };
    }

    /**
     * @return string[]
     */
    private function neonTagNames(Coyote\Job $jobOffer): array {
        return $jobOffer->tags->pluck('name')->toArray();
    }

    /**
     * @return int[]
     */
    private function neonTagPriorities(Coyote\Job $jobOffer): array {
        return $jobOffer->tags->pluck('pivot.priority')->toArray();
    }

    private function neonWorkModeRemoteRange(Job $jobOffer): int {
        if ($jobOffer->is_remote) {
            return $jobOffer->remote_range ?? 0;
        }
        return 0;
    }

    private function neonLocations(Job $jobOffer): array {
        return $jobOffer->locations
            ->map($this->neonLocation(...))
            ->filter(fn(?JobOfferLocation $location) => $location?->city !== null)
            ->values()
            ->toArray();
    }

    private function neonLocation(Coyote\Job\Location $location): ?JobOfferLocation {
        if (!$location->latitude || !$location->longitude) {
            return null;
        }
        return new JobOfferLocation(
            $location->latitude,
            $location->longitude,
            $location->city,
            $location->street,
            $location->street_number,
            $location->country?->code,
            null);
    }

    private function neonCompanyAddress(Coyote\Firm $firm): ?JobOfferLocation {
        if (!$firm->latitude || !$firm->longitude) {
            return null;
        }
        return new JobOfferLocation(
            $firm->latitude,
            $firm->longitude,
            $firm->city,
            $firm->street,
            $firm->street_number,
            $firm->country?->code,
            $firm->postcode);
    }
}
