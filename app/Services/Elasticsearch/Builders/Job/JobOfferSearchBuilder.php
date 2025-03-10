<?php
namespace Coyote\Services\Elasticsearch\Builders\Job;

use Coyote\Services\Elasticsearch\Filters;
use Coyote\Services\Geocoder;
use Illuminate\Http\Request;

class JobOfferSearchBuilder
{
    private string $sort;

    public function __construct(private JobOfferSearchQueryBuilder $builder) {}

    public function sortBySalary(): void
    {
        $this->sort = 'salary';
    }

    public function sortByPublishDate(): void
    {
        $this->sort = 'boost_at';
    }

    public function sortByRelevance(): void
    {
        $this->sort = '_score';
    }

    public function addLocation(string $location): void
    {
        $this->builder->city->addCity($location);
    }

    public function addTag(string $tagName): void
    {
        $this->builder->tag->addTag($tagName);
    }

    public function addCompany(string $companySlug): void
    {
        $this->builder->must(new Filters\Job\Firm($companySlug));
    }

    public function remote(bool $hasRemoteRange): void
    {
        $this->builder->addRemoteFilter($hasRemoteRange);
    }

    public function boostLocation(?Geocoder\Location $location): void
    {
        $this->builder->should(new Filters\Job\LocationScore($location));
    }

    public function usedTags(): array
    {
        return $this->builder->tag->getTags();
    }

    public function usedLocations(): array
    {
        return $this->builder->city->getCities();
    }

    public function buildQueryFromRequest(Request $request): array
    {
        return $this->builder->buildQueryData(
            $this->sort,
            max(0, (int)$request->get('page', 1) - 1),
            15,
            $request->get('q', null),
            $request->get('city'),
            array_filter($request->get('locations', [])),
            array_filter($request->get('tags', [])),
            $request->filled('remote', false),
            $request->filled('remote_range', false),
            $this->salary($request),
            (int)$request->get('currency'),
        );
    }

    public function buildQueryData(
        int     $page,
        int     $perPage,
        ?string $searchPhrase,
        ?string $cityLocation,
        ?array  $locations,
        ?array  $tags,
        bool    $isRemote,
        bool    $hasRemoteRange,
        ?int    $minSalary,
        ?int    $currency,
    ): array
    {
        return $this->builder->buildQueryData(
            $this->sort, $page, $perPage, $searchPhrase, $cityLocation,
            $locations, $tags, $isRemote, $hasRemoteRange, $minSalary, $currency,
        );
    }

    private function salary(Request $request): ?int
    {
        if ($request->filled('salary')) {
            $salary = $request->get('salary');
            if (\is_string($salary) && \ctype_digit($salary)) {
                return (int)$salary;
            }
        }
        return null;
    }
}
