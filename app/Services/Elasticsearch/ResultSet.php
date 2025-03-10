<?php
namespace Coyote\Services\Elasticsearch;

use Illuminate\Support;
use Traversable;

class ResultSet implements \Countable, \IteratorAggregate
{
    /**
     * @var array|\Illuminate\Support\Collection
     */
    protected $hits = [];

    /**
     * @var array
     */
    protected $aggregations = [];

    /**
     * @var int
     */
    protected $total = 0;

    /**
     * @var int
     */
    protected $took;

    /**
     * @param array $response
     */
    public function __construct($response)
    {
        if (isset($response['hits'])) {
            $this->hits = $this->collect($this->map($response['hits']['hits']));
            $this->total = $response['hits']['total'];
            $this->took = $response['took'];

            if (isset($response['aggregations'])) {
                $this->aggregations = $response['aggregations'];
            }
        }
    }

    /**
     * Additional data transformation.
     *
     * @param array $data
     * @return array
     */
    protected function map(array $data)
    {
        return $data;
    }

    /**
     * Transform results array to laravel's collection
     *
     * @param mixed $data
     * @return \Illuminate\Support\Collection
     */
    protected function collect($data)
    {
        $data = collect($data);

        foreach ($data as $key => $item) {
            if (is_array($item)) {
                $data[$key] = $this->collect($item);
            }
        }

        return $data;
    }

    /**
     * Total Hits
     *
     * @return int
     */
    public function total()
    {
        return $this->total;
    }

    /**
     * @return int
     */
    public function took()
    {
        return $this->took;
    }

    /**
     * Get Hits
     *
     * Get the raw hits from
     * Elasticsearch results.
     *
     * @return array|\Illuminate\Support\Collection
     */
    public function getHits()
    {
        return $this->hits;
    }

    public function getSource(): Support\Collection
    {
        if ($this->total) {
            return $this->hits->pluck('_source');
        }
        return Support\Collection::empty();
    }

    public function __call($name, $arguments)
    {
        return $this->hits->$name(...$arguments);
    }

    public function count(): int
    {
        return $this->hits->count();
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getHighlights()
    {
        return $this->hits->pluck('highlight', '_source.id');
    }

    /**
     * @param string $name
     * @return \Illuminate\Support\Collection|array
     */
    public function getAggregationCount($name)
    {
        if (!array_has($this->aggregations, $name)) {
            return [];
        }

        $data = array_get($this->aggregations, "$name.buckets");

        return collect($data)->pluck('key');
    }

    /**
     * @param string $name
     * @param string $key
     * @return \Illuminate\Support\Collection|array
     */
    public function getAggregationHits($name, $key)
    {
        if (!isset($this->aggregations[$name])) {
            return [];
        }

        $collection = collect();

        foreach ($this->aggregations[$name]['buckets'] as $bucket) {
            if ($bucket['key'] == $key) {
                $collection = $this->collect($bucket[$name]['hits']['hits']);
            }
        }

        return $collection->pluck('_source');
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function getAggregations($name)
    {
        return array_get($this->aggregations, $name);
    }

    public function getIterator(): Traversable
    {
        return new \ArrayIterator($this->hits->pluck('_source')->toArray());
    }
}
