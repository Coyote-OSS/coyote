<?php
namespace Coyote\Providers\Neon;

use Coyote\Job;
use Coyote\Repositories\Criteria\EagerLoading;
use Coyote\Repositories\Criteria\EagerLoadingWithCount;
use Coyote\Repositories\Criteria\Job\IncludeSubscribers;
use Coyote\Repositories\Eloquent\JobRepository;
use Coyote\Services\Elasticsearch\Builders\Job\SearchBuilder;
use Coyote\Services\Elasticsearch\ResultSet;
use Illuminate\Database\Eloquent;
use Illuminate\Http\Request;
use Neon;

class JobElasticSearchRepository
{
    public function __construct(private JobRepository $jobs, private Request $request) {}

    /**
     * @return Eloquent\Collection|Job[]
     */
    public function jobOffers(): Eloquent\Collection
    {
        /** @var ResultSet $result */
        $result = $this->jobs->search($this->searchBuilder());
        $this->jobs->pushCriteria(new EagerLoading(['firm', 'locations', 'tags', 'currency']));
        $this->jobs->pushCriteria(new EagerLoadingWithCount(['comments']));
        $this->jobs->pushCriteria(new IncludeSubscribers(auth()->id()));
        return $this->jobs->findManyWithOrder($result->getSource()->pluck('id')->unique()->toArray());
    }

    private function searchBuilder(): SearchBuilder
    {
        /** @var SearchBuilder $builder */
        $builder = app(SearchBuilder::class);
        $builder->boostLocation($this->request->attributes->get('geocode'));
        $builder->setSort(SearchBuilder::DEFAULT_SORT);
        return $builder;
    }
}
