<?php
namespace Coyote\Providers\Neon;

use Carbon\Carbon;
use Coyote;
use Coyote\Repositories\Criteria\EagerLoading;
use Coyote\Repositories\Criteria\EagerLoadingWithCount;
use Coyote\Repositories\Eloquent\JobRepository;
use Coyote\Services\Elasticsearch\Builders\Job\JobOfferSearchBuilder;
use Coyote\Services\Elasticsearch\Builders\Job\SearchQueryBuilder;
use Coyote\Services\UrlBuilder;
use Illuminate\Database\Eloquent;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Neon;

class JobElasticSearchRepository
{
    public function __construct(private JobRepository $jobs, private Request $request) {}

    /**
     * @return Neon\View\JobOffer[]
     */
    public function jobOffers(): array
    {
        return $this->coyoteJobOffers()
            ->map($this->neonJobOffer(...))
            ->toArray();
    }

    private function coyoteJobOffers(): Eloquent\Collection
    {
        $this->jobs->pushCriteria(new EagerLoading(['firm', 'locations', 'tags', 'currency']));
        $this->jobs->pushCriteria(new EagerLoadingWithCount(['comments']));
        $ids = $this->searchJobIds();
        if (empty($ids)) {
            return Eloquent\Collection::empty();
        }
        return $this->jobs->findManyWithOrder($ids);
    }

    private function neonJobOffer(Coyote\Job $jobOffer): Neon\View\JobOffer
    {
        return new Neon\View\JobOffer(
            $jobOffer->title,
            UrlBuilder::job($jobOffer, true),
            $this->locationCities($jobOffer),
            $this->workMode($jobOffer),
            $this->isSubscribed($jobOffer),
            $jobOffer->boost_at->diffInDays(Carbon::now()) <= 2,
            $jobOffer->boost_at->format('Y-m-d h:i'),
            $this->jobOfferTags($jobOffer),
            $this->trimToNull($jobOffer->firm->name),
            $this->trimToNull((string)$jobOffer->firm->logo->url()),
            $jobOffer->comments_count,
            $jobOffer->salary_from,
            $jobOffer->salary_to,
            Neon\View\Currency::from($jobOffer->currency->name),
            $jobOffer->is_gross,
            Neon\View\Settlement::from($jobOffer->rate));
    }

    private function isSubscribed(Coyote\Job $jobOffer): bool
    {
        return $this
            ->subscribedJobOffers()
            ->filter(fn(Coyote\Job $offer) => $offer->id === $jobOffer->id)
            ->isNotEmpty();
    }

    private function subscribedJobOffers(): Eloquent\Collection
    {
        if (!auth()->check()) {
            return Collection::empty();
        }
        return $this->jobs->subscribes(auth()->id());
    }

    private function searchBuilder(): SearchQueryBuilder
    {
        $jobSearch = new JobOfferSearchBuilder(app(SearchQueryBuilder::class));
        $jobSearch->boostLocation($this->request->attributes->get('geocode'));
        $jobSearch->sortByPublishDate();
        return $jobSearch->builder;
    }

    private function workMode(Coyote\Job $jobOffer): Neon\View\WorkMode
    {
        if (!$jobOffer->is_remote) {
            return Neon\View\WorkMode::Stationary;
        }
        if ($jobOffer->remote_range === 100) {
            return Neon\View\WorkMode::FullyRemote;
        }
        return Neon\View\WorkMode::Hybrid;
    }

    /**
     * @return string[]
     */
    private function locationCities(Coyote\Job $jobOffer): array
    {
        return $jobOffer->locations
            ->map(fn(Coyote\Job\Location $location) => $location->city)
            ->filter(fn(string $city) => !empty($city))
            ->toArray();
    }

    /**
     * @return string[]
     */
    private function jobOfferTags(Coyote\Job $jobOffer): array
    {
        return $jobOffer->tags
            ->map(fn(Coyote\Tag $tag) => $tag->real_name ?? $tag->name)
            ->toArray();
    }

    private function trimToNull(?string $string): ?string
    {
        $trimmed = \trim($string);
        if ($trimmed === '') {
            return null;
        }
        return $trimmed;
    }

    private function searchJobIds(): array
    {
        $result = $this->jobs->search($this->searchBuilder());
        return $result->getSource()->pluck('id')->unique()->toArray();
    }
}
