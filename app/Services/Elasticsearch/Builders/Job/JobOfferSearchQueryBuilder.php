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

final class JobOfferSearchQueryBuilder extends QueryBuilder
{
    public Filters\Job\City $city;
    public Filters\Job\Location $location;
    public Filters\Job\Tag $tag;

    public function __construct()
    {
        $this->city = new Filters\Job\City();
        $this->tag = new Filters\Job\Tag();
        $this->location = new Filters\Job\Location();
    }

    public function addRemoteFilter(bool $hasRemoteRange): void
    {
        // @see https://github.com/adam-boduch/coyote/issues/374
        // jezeli szukamy ofert pracy zdalnej ORAZ z danego miasta, stosujemy operator OR zamiast AND
        $method = count($this->city->getCities()) ? 'should' : 'must';
        $this->$method(new Filters\Job\Remote());
        if ($hasRemoteRange) {
            $this->$method(new Filters\Job\RemoteRange());
        }
    }

    public function buildQueryData(
        string  $sort,
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
        if ($searchPhrase) {
            $this->must(new MultiMatch($searchPhrase, ['title^3', 'description', 'recruitment', 'tags^2', 'firm.name']));
        } else {
            // no keywords were provided -- let's calculate score based on score functions
            // wazniejsze sa te ofery, ktorych pole score jest wyzsze. obliczamy to za pomoca wzoru: log(score * 1)
            $this->score(new FieldValueFactor('score', 'log', 1));
            // strsze ogloszenia traca na waznosci po 14d. z kazdym dniem score bedzie malalo o 1/10
            // za wyjatkiem pierwszych 2h publikacji
            $this->score(new Decay('boost_at', '14d', 0.1, '2h'));
            $this->must(new MatchAll());
        }
        if ($cityLocation) {
            $this->city->addCity($cityLocation);
        }
        if ($locations) {
            $this->city->addCity($locations);
        }
        if ($tags) {
            $this->tag->addTag($tags);
        }
        if ($minSalary) {
            $this->must(new Filters\Range('salary', ['gte' => $minSalary]));
            $this->must(new Filters\Job\Currency($currency));
        }
        if ($isRemote) {
            $this->addRemoteFilter($hasRemoteRange);
        }
        $this->must(new Filters\Term('model', class_basename(Job::class)));
        $this->score(new ScriptScore('_score'));
        $this->sort(new Sort($sort, 'desc'));
        $this->must($this->city);
        $this->must($this->tag);
        $this->must($this->location);
        $this->aggs(new Aggs\Job\Location());
        $this->aggs(new Aggs\Job\TopSpot());
        $this->size($perPage * $page, $perPage);
        $this->source(['id']);
        return parent::build();
    }
}
