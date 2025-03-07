<?php
namespace Coyote\Services\Elasticsearch\Builders\Job;

use Coyote\Services\Geocoder;

class JobOfferSearchBuilder
{
    public function __construct(public SearchQueryBuilder $builder) {}

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
        $this->builder->addFirmFilter($companySlug);
    }

    public function remote(): void
    {
        $this->builder->addRemoteFilter();
    }

    public function sortBySalary(): void
    {
        $this->builder->setSort('salary');
    }

    public function sortByPublishDate(): void
    {
        $this->builder->setSort('boost_at');
    }

    public function sortByRelevance(): void
    {
        $this->builder->setSort('_score');
    }

    public function boostLocation(?Geocoder\Location $location): void
    {
        $this->builder->boostLocation($location);
    }

    public function usedTags(): array
    {
        return $this->builder->tag->getTags();
    }

    public function usedLocations(): array
    {
        return $this->builder->city->getCities();
    }
}
