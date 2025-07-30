<?php
namespace Coyote\Services\Elasticsearch\Builders\Job;

use Coyote\Job;
use Coyote\Services\Elasticsearch\Filters\Term;
use Coyote\Services\Elasticsearch\Functions\FieldValueFactor;
use Coyote\Services\Elasticsearch\Functions\Random;
use Coyote\Services\Elasticsearch\QueryBuilder;
use Coyote\Services\Elasticsearch\SimpleQueryString;

class FreeJobsSearchBuilder extends SearchBuilder implements JobSearchBuilder {
    public function boostTags(string $tag): void {
        $this->must(new SimpleQueryString($tag, ['title^2', 'tags.original'], 3));
    }

    public function build(): array {
        $this->mustNot(new Term('is_ads', true));
        $this->must(new Term('model', class_basename(Job::class)));

        $this->score(new FieldValueFactor('score', 'log', 1));
        $this->score(new Random());
        $this->size(0, 4);

        $this->source([
            'id',
            'title',
            'slug',
            'is_remote',
            'remote_range',
            'firm.*',
            'locations',
            'tags',
            'currency_symbol',
            'salary_from',
            'salary_to',
        ]);

        return QueryBuilder::build();
    }
}
