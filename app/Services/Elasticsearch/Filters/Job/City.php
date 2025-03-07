<?php
namespace Coyote\Services\Elasticsearch\Filters\Job;

use Coyote\Services\Elasticsearch\DslInterface;
use Coyote\Services\Elasticsearch\Filter;
use Coyote\Services\Elasticsearch\QueryBuilderInterface;

class City extends Filter implements DslInterface
{
    protected array $cities = [];

    public function __construct($cities = [])
    {
        $this->setCities($cities);
    }

    public function addCity($city): void
    {
        if (is_array($city)) {
            foreach ($city as $value) {
                $this->addCity($value);
            }
        } else {
            $this->cities = array_merge($this->cities, new \Coyote\Services\Helper\City()->grab($city));
        }
    }

    public function setCities($cities): void
    {
        if (!is_array($cities)) {
            $cities = [$cities];
        }
        $this->cities = $cities;
    }

    public function getCities(): array
    {
        return $this->cities;
    }

    public function apply(QueryBuilderInterface $queryBuilder)
    {
        if (empty($this->cities)) {
            return (object)[];
        }
        return [
            'nested' => [
                'path'  => 'locations',
                'query' => [
                    'match' => [
                        'locations.label' => implode(' ', $this->cities),
                    ],
                ],
            ],
        ];
    }
}
