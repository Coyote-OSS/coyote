<?php
namespace Coyote\Services\Elasticsearch\Builders\Job;

use Coyote\Job;
use Coyote\Services\Elasticsearch\Aggs;
use Coyote\Services\Elasticsearch\Filters;
use Coyote\Services\Elasticsearch\Functions\Decay;
use Coyote\Services\Elasticsearch\Functions\FieldValueFactor;
use Coyote\Services\Elasticsearch\Functions\ScriptScore;
use Coyote\Services\Elasticsearch\MatchAll;
use Coyote\Services\Elasticsearch\MultiMatch;
use Coyote\Services\Elasticsearch\QueryBuilder;
use Coyote\Services\Elasticsearch\Sort;
use Coyote\Services\Geocoder\Location;
use Illuminate\Http\Request;

class SearchBuilder extends QueryBuilder {
    const PER_PAGE = 15;
    const DEFAULT_SORT = 'boost_at';
    const SCORE = '_score';

    /**
     * @var Filters\Job\City
     */
    public $city;

    /**
     * @var Filters\Job\Location
     */
    public $location;

    /**
     * @var Filters\Job\Tag
     */
    public $tag;

    /**
     * @var array
     */
    protected $languages = [];

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var string
     */
    protected $sort;

    /**
     * @param Request $request
     */
    public function __construct(Request $request) {
        $this->request = $request;

        $this->city = new Filters\Job\City();
        $this->tag = new Filters\Job\Tag();
        $this->location = new Filters\Job\Location();
    }

    /**
     * @param string $sort
     */
    public function setSort($sort) {
        $this->sort = in_array($sort, ['boost_at', '_score', 'salary']) ? $sort : self::DEFAULT_SORT;
    }

    /**
     * @param Location|null $location
     */
    public function boostLocation(Location $location = null) {
        $this->should(new Filters\Job\LocationScore($location));
    }

    /**
     * Apply remote job filter
     */
    public function addRemoteFilter() {
        // @see https://github.com/adam-boduch/coyote/issues/374
        // jezeli szukamy ofert pracy zdalnej ORAZ z danego miasta, stosujemy operator OR zamiast AND
        $method = count($this->city->getCities()) ? 'should' : 'must';

        $this->$method(new Filters\Job\Remote());

        if ($this->request->filled('remote_range')) {
            $this->$method(new Filters\Job\RemoteRange());
        }
    }

    /**
     * @param string $name
     */
    public function addFirmFilter($name) {
        $this->must(new Filters\Job\Firm($this->filterString($name)));
    }

    public function build(): array {
        $this->addFilters();
        $this->size(
            self::PER_PAGE * max(0, (int)$this->request->get('page', 1) - 1), 
            self::PER_PAGE);
        return parent::build();
    }

    public function buildUnlimited(int $start, int $pageSize): array {
        $this->addFilters();
        $this->size($start, $pageSize);
        return parent::build();
    }

    protected function setupFilters() {
        $this->must($this->city);
        $this->must($this->tag);
        $this->must($this->location);
    }

    protected function setupScoreFunctions() {
        // wazniejsze sa te ofery, ktorych pole score jest wyzsze. obliczamy to za pomoca wzoru: log(score * 1)
        $this->score(new FieldValueFactor('score', 'log', 1));
        // strsze ogloszenia traca na waznosci, glownie po 14d. z kazdym dniem score bedzie malalo o 1/10
        // za wyjatkiem pierwszych 2h publikacji
        $this->score(new Decay('boost_at', '14d', 0.1, '2h'));
    }

    protected function setupAggregations(): void {
        $this->aggs(new Aggs\Job\Location());
        $this->aggs(new Aggs\Job\TopSpot());
    }

    private function addSalaryFilter(int $salary, int $currencyId): void {
        $this->must(new Filters\Range('salary', ['gte' => $salary]));
        $this->must(new Filters\Job\Currency($currencyId));
    }

    private function filterString(string $value): string {
        return \filter_var($value, \FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    }

    private function addFilters(): void {
        $searchPhrase = $this->request->get('q');
        $this->must(new Filters\Term('model', class_basename(Job::class)));

        if ($searchPhrase) {
            $this->must(new MultiMatch(
                $this->filterString($searchPhrase),
                ['title^3', 'description', 'recruitment', 'tags^2', 'firm.name']),
            );
        } else {
            // no keywords were provided -- let's calculate score based on score functions
            $this->setupScoreFunctions();
            $this->must(new MatchAll());
        }

        if ($this->request->filled('city')) {
            $cities = $this->request->get('city');
            if (!\is_array($cities)) {
                $cities = [$cities];
            }
            foreach ($cities as $city) {
                if ($city) {
                    $this->city->addCity($this->filterString($city));
                }
            }
        }

        if ($this->request->filled('locations')) {
            $this->city->addCity(array_filter($this->request->get('locations')));
        }

        if ($this->request->filled('tags')) {
            $this->tag->addTag(array_filter($this->request->get('tags')));
        }

        if ($this->request->filled('salary')) {
            $salary = $this->request->get('salary');
            if (\is_string($salary) && \ctype_digit($salary)) {
                $this->addSalaryFilter(
                    (int)$salary,
                    (int)$this->request->get('currency'),
                );
            }
        }

        if ($this->request->filled('remote')) {
            $this->addRemoteFilter();
        }

        $this->score(new ScriptScore('_score'));
        $this->sort(new Sort($this->sort, 'desc'));
        $this->setupFilters();
        $this->setupAggregations();
        $this->source(['id']);
    }
}
