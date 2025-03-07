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
use Coyote\Services\Geocoder;
use Illuminate\Http\Request;

/**
 * @deprecated
 */
final class JobOfferSearchQueryBuilder extends QueryBuilder
{
    public Filters\Job\City $city;
    public Filters\Job\Location $location;
    public Filters\Job\Tag $tag;
    /**
     * @deprecated
     */
    private Request $request;
    private string $sort;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->city = new Filters\Job\City();
        $this->tag = new Filters\Job\Tag();
        $this->location = new Filters\Job\Location();
    }

    public function setSort(string $sort): void
    {
        $this->sort = $sort;
    }

    public function boostLocation(?Geocoder\Location $location = null): void
    {
        $this->should(new Filters\Job\LocationScore($location));
    }

    public function addRemoteFilter(): void
    {
        // @see https://github.com/adam-boduch/coyote/issues/374
        // jezeli szukamy ofert pracy zdalnej ORAZ z danego miasta, stosujemy operator OR zamiast AND
        $method = count($this->city->getCities()) ? 'should' : 'must';
        $this->$method(new Filters\Job\Remote());
        if ($this->request->filled('remote_range')) {
            $this->$method(new Filters\Job\RemoteRange());
        }
    }

    public function addFirmFilter(string $name): void
    {
        $this->must(new Filters\Job\Firm($name));
    }

    public function build(): array
    {
        if ($this->request->filled('q')) {
            $this->must(new MultiMatch(
                $this->request->get('q'),
                ['title^3', 'description', 'recruitment', 'tags^2', 'firm.name']));
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
                    $this->city->addCity($city);
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
                $this->addSalaryFilter((int)$salary);
                $this->addCurrencyFilter((int)$this->request->get('currency'));
            }
        }

        if ($this->request->filled('remote')) {
            $this->addRemoteFilter();
        }

        $this->must(new Filters\Term('model', class_basename(Job::class)));
        $this->score(new ScriptScore('_score'));
        $this->sort(new Sort($this->sort, 'desc'));
        $this->must($this->city);
        $this->must($this->tag);
        $this->must($this->location);
        $this->aggs(new Aggs\Job\Location());
        $this->aggs(new Aggs\Job\TopSpot());
        $perPage = 15;
        $this->size($perPage * (max(0, (int)$this->request->get('page', 1) - 1)), $perPage);
        $this->source(['id']);

        return parent::build();
    }

    private function setupScoreFunctions(): void
    {
        // wazniejsze sa te ofery, ktorych pole score jest wyzsze. obliczamy to za pomoca wzoru: log(score * 1)
        $this->score(new FieldValueFactor('score', 'log', 1));
        // strsze ogloszenia traca na waznosci, glownie po 14d. z kazdym dniem score bedzie malalo o 1/10
        // za wyjatkiem pierwszych 2h publikacji
        $this->score(new Decay('boost_at', '14d', 0.1, '2h'));
    }

    private function addSalaryFilter(int $salary): void
    {
        $this->must(new Filters\Range('salary', ['gte' => $salary]));
    }

    private function addCurrencyFilter(int $currencyId): void
    {
        $this->must(new Filters\Job\Currency($currencyId));
    }
}
