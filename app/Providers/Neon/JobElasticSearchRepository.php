<?php
namespace Coyote\Providers\Neon;

use Coyote\Repositories\Criteria\EagerLoading;
use Coyote\Repositories\Criteria\EagerLoadingWithCount;
use Coyote\Repositories\Eloquent\JobRepository;
use Coyote\Services\Elasticsearch\Builders\Job\SearchBuilder;
use Coyote\Services\Elasticsearch\ResultSet;
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
        /** @var ResultSet $result */
        $result = $this->jobs->search($this->searchBuilder());
        $this->jobs->pushCriteria(new EagerLoading(['firm', 'locations', 'tags', 'currency']));
        $this->jobs->pushCriteria(new EagerLoadingWithCount(['comments']));
//        $this->jobs->pushCriteria(new IncludeSubscribers(auth()->id()));
        $jobOffers = [];
        foreach ($this->jobs->findManyWithOrder($result->getSource()->pluck('id')->unique()->toArray()) as $jobOffer) {
            $jobOffers[] = new Neon\View\JobOffer(
                $jobOffer->title,
                $jobOffer->firm->name,
                $jobOffer->comments_count);
        }
        return $jobOffers;
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
