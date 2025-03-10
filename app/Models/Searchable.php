<?php
namespace Coyote;

use Coyote\Services\Elasticsearch\QueryBuilderInterface;
use Coyote\Services\Elasticsearch\ResultSet;
use Elasticsearch;

/**
 * @deprecated
 */
trait Searchable
{
    public function search(QueryBuilderInterface $queryBuilder): ResultSet
    {
        return $this->searchBody($queryBuilder->build());
    }

    private function searchBody(array $body): ResultSet
    {
        return new ResultSet($this->elasticsearchClient()->search([
            'index' => config('elasticsearch.default_index'),
            'type'  => '_doc',
            ...$this->modelId(),
            'body'  => $body,
        ]));
    }

    private function modelId(): array
    {
        if ($this->getKey()) {
            return ['id' => str_singular($this->getTable()) . '_' . $this->getKey()];
        }
        return [];
    }

    private function elasticsearchClient(): Elasticsearch\Client
    {
        return app('elasticsearch');
    }

    abstract public function getTable();

    abstract public function getKey();
}
