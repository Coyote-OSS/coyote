<?php
namespace Coyote\Services\Elasticsearch\Filters\Job;

use Coyote\Services\Elasticsearch\Filter;
use Coyote\Services\Elasticsearch\QueryBuilderInterface;
use Coyote\Services\Geocoder;
use Coyote\Services\Geocoder\Location as GeocoderLocation;

class LocationScore extends Filter
{
    const WEIGHT = '0.2';
    const OFFSET = '20km';
    const SCALE = '100km';
    const DECAY = '0.5';

    public function __construct(?Geocoder\Location $value)
    {
        parent::__construct('locations.coordinates', $value);
    }

    public function apply(QueryBuilderInterface $queryBuilder)
    {
        if (!$this->value instanceof GeocoderLocation) {
            return (object)[];
        }

        if (!$this->value->isValid()) {
            return (object)[];
        }

        return [
            'nested' => [
                'path'  => $this->path(),
                'query' => [
                    'function_score' => [
                        'score_mode' => 'sum',
                        'functions'  => [
                            [
                                'gauss'  => [
                                    $this->field => [
                                        'origin' => [
                                            'lat' => $this->value->latitude,
                                            'lon' => $this->value->longitude,
                                        ],
                                        'scale'  => self::SCALE,
                                        'offset' => self::OFFSET,
                                        'decay'  => self::DECAY,
                                    ],
                                ],
                                'weight' => self::WEIGHT,
                            ],
                            [
                                'script_score' => [
                                    'script' => '1', // praca zdalna, brak lokalizacji. score bedzie rowne 1?
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    private function path(): string
    {
        return explode('.', $this->field)[0];
    }
}
