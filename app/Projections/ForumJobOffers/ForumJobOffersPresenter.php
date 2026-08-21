<?php
namespace Coyote\Projections\ForumJobOffers;

use Carbon\Carbon;
use Coyote;
use Coyote\Job;
use Coyote\Repositories\Eloquent\JobRepository;
use Web\Projections\ForumJobOffers\ViewModel;
use Web\Projections\ForumJobOffers\ViewModel\ForumJobOfferTile;

readonly class ForumJobOffersPresenter {
    public function __construct(private JobRepository $jobRepository) {}

    /**
     * @return ForumJobOfferTile[]
     */
    public function forumJobOffers(): array {
        return $this->jobRepository
            ->listJobOffers(null, null)
            ->load(['firm', 'tags', 'currency', 'locations'])
            ->map($this->formatJobEloquentModel(...))
            ->toArray();
    }

    private function formatJobEloquentModel(Job $job): ForumJobOfferTile {
        return new ForumJobOfferTile(
            companyName:$job->firm->name,
            companyLogoUrl:$this->formatLogoUrl($job->firm->logo),
            jobOfferHref:route('neon.jobOffer.show', [$job->slug, $job->id]),
            jobOfferTitle:$job->title,
            headerPills:$this->headerPills($job),
            salaryFormat:$this->formatSalary($job),
            salaryDisclosed:$job->salary_from || $job->salary_to,
            isNew:$this->isNew($job),
            technologyTags:$job->tags->map($this->formatTagEloquentModel(...))->values()->toArray(),
        );
    }

    private function isNew(Job $job): bool {
        return carbon($job->boost_at)->diffInDays(Carbon::now()) <= 2;
    }

    private function formatTagEloquentModel(Coyote\Tag $tag): ViewModel\Tag {
        return new ViewModel\Tag(
            name:$tag->name,
            logoUrl:$this->formatLogoUrl($tag->logo),
        );
    }

    /**
     * @return string[]
     */
    private function headerPills(Job $job): array {
        $pills = $job->locations->pluck('city')->filter()->values()->toArray();
        if ($job->is_remote) {
            $pills[] = 'Remote';
        }
        return $pills;
    }

    private function formatSalary(Job $job): string {
        if (!$job->salary_from && !$job->salary_to) {
            return 'Nie podano $$$';
        }
        $range = $this->formatSalaryRange($job);
        $tax = $this->formatSalaryTax($job);
        $rate = $this->formatSalaryRate($job);
        return "$range $job->currency_symbol $tax / $rate";
    }

    private function formatSalaryRate(Job $job): string {
        return Job::getRatesList()[$job->rate];
    }

    private function formatSalaryTax(Job $job): string {
        return $job->is_gross ? 'brutto' : 'netto';
    }

    private function formatSalaryRange(Job $job): string {
        if ($job->salary_from && $job->salary_to) {
            return "$job->salary_from - $job->salary_to";
        }
        if ($job->salary_from) {
            return "od $job->salary_from";
        }
        return "do $job->salary_to";
    }

    private function formatLogoUrl(Coyote\Services\Media\File $file): ?string {
        if ($file->getFilename()) {
            return (string)$file->url();
        }
        return null;
    }
}
