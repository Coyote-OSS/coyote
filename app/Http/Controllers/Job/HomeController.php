<?php
namespace Coyote\Http\Controllers\Job;

use Carbon\Carbon;
use Coyote\Currency;
use Coyote\Domain\RouteVisits;
use Coyote\Http\Presenter\UserPlanBundlePresenter;
use Coyote\Http\Resources\JobResource;
use Coyote\Http\Resources\TagResource;
use Coyote\Repositories\Criteria\EagerLoading;
use Coyote\Repositories\Criteria\EagerLoadingWithCount;
use Coyote\Repositories\Criteria\Job\IncludeSubscribers;
use Coyote\Repositories\Criteria\Job\PriorDeadline;
use Coyote\Repositories\Criteria\Tag\ForCategory;
use Coyote\Repositories\Eloquent\JobRepository;
use Coyote\Repositories\Eloquent\TagRepository;
use Coyote\Services\Elasticsearch\Builders\Job\SearchQueryBuilder;
use Coyote\Tag;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Jenssegers\Agent\Agent;

class HomeController extends \Coyote\Http\Controllers\Controller
{
    /**
     * @var string
     */
    private $firmName;

    public function __construct(
        private JobRepository      $job,
        private TagRepository      $tag,
        private SearchQueryBuilder $builder,
    )
    {
        parent::__construct();
        $this->breadcrumb->push('Praca', route('job.home'));
    }

    public function index(): View
    {
        return $this->load();
    }

    public function city($name): View
    {
        $this->builder->city->addCity($name);
        return $this->load();
    }

    public function tag($name): View
    {
        $this->builder->tag->addTag(Str::lower($name));
        return $this->load();
    }

    public function firm(string $slug): View
    {
        $this->builder->addFirmFilter($slug);
        $this->firmName = $slug;
        return $this->load();
    }

    public function remote(): View
    {
        $this->builder->addRemoteFilter();
        return $this->load();
    }

    private function load(): View
    {
        $visits = app(RouteVisits::class);
        $agent = new Agent();
        if (!$agent->isRobot($this->request->userAgent())) {
            $visits->visit($this->request->path(), Carbon::now()->toDateString());
        }
        // set sort by score if keyword was provided and no sort was specified
        $defaultSort = $this->request->input('sort', $this->request->filled('q') ? SearchQueryBuilder::SCORE : SearchQueryBuilder::DEFAULT_SORT);

        $this->builder->boostLocation($this->request->attributes->get('geocode'));
        $this->builder->setSort($defaultSort);

        $result = $this->job->search($this->builder);

        // keep in mind that we return data by calling getSource(). This is important because
        // we want to pass collection to the twig (not raw php array)
        /** @var Collection $source */
        $source = $result->getSource();
        $eagerCriteria = new EagerLoading(['firm', 'locations', 'tags', 'currency']);
        $this->job->pushCriteria($eagerCriteria);
        $this->job->pushCriteria(new EagerLoadingWithCount(['comments']));
        $this->job->pushCriteria(new IncludeSubscribers($this->userId));
        $jobs = [];
        if (count($source)) {
            $premium = $result->getAggregationHits('premium_listing', true);
            $premium = array_first($premium); // only one premium at the top
            if ($premium) {
                $source->prepend($premium);
            }
            $ids = $source->pluck('id')->unique()->toArray();
            $jobs = $this->job->findManyWithOrder($ids);
        }
        $pagination = new LengthAwarePaginator(
            $jobs,
            $result->total(),
            SearchQueryBuilder::PER_PAGE,
            LengthAwarePaginator::resolveCurrentPage(),
            ['path' => LengthAwarePaginator::resolveCurrentPath()]);
        $pagination->appends($this->request->except('page'));
        $this->job->resetCriteria();
        $this->job->pushCriteria($eagerCriteria);
        $this->job->pushCriteria(new PriorDeadline());
        $this->tag->pushCriteria(new ForCategory(Tag\Category::LANGUAGE));

        /** @var UserPlanBundlePresenter $presenter */
        $presenter = app(UserPlanBundlePresenter::class);
        return $this->view('job.home', [
            'input'          => [
                ...$this->request->all('q', 'city', 'sort', 'salary', 'currency', 'remote_range', 'page'),
                'tags'      => $this->builder->tag->getTags(),
                'locations' => $this->builder->city->getCities(),
                'remote'    => $this->request->filled('remote') || $this->request->route()->getName() === 'job.remote' ? true : null,
            ],
            'url'            => $this->fullUrl($this->request->except('timestamp')),
            'defaults'       => [
                'sort'     => $defaultSort,
                'currency' => Currency::PLN,
            ],
            'locations'      => $result->getAggregationCount("global.locations.locations_city_original")->slice(0, 10)->filter(),
            'tags'           => TagResource::collection($this->tag->all())->toArray($this->request),
            'jobs'           => JobResource::collection($pagination)->toResponse($this->request)->getData(true),
            'subscribed'     => $this->getSubscribed(),
            'currencies'     => (object)Currency::all('name', 'id', 'symbol')->keyBy('id'),
            'firm'           => $this->firmName,
            'userPlanBundle' => $presenter->userPlanBundle(),
        ]);
    }

    private function getSubscribed(): array
    {
        if (!$this->userId) {
            return [];
        }
        return JobResource::collection($this->job->subscribes($this->userId))->toArray($this->request);
    }

    private function fullUrl(array $query): string
    {
        if ($this->request->getBaseUrl() . $this->request->getPathInfo() === '/') {
            $question = '/?';
        } else {
            $question = '?';
        }
        return $this->request->url() . $this->query($query, $question);
    }

    private function query(array $query, string $question): string
    {
        if (count($query)) {
            return $question . http_build_query($query);
        }
        return '';
    }
}
