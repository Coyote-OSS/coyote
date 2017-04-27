<?php

namespace Coyote\Services\Elasticsearch\Functions;

use Coyote\Services\Elasticsearch\FunctionScore;
use Coyote\Services\Elasticsearch\QueryBuilderInterface;

class Decay extends FunctionScore
{
    /**
     * @var string
     */
    protected $field;

    /**
     * @var string
     */
    protected $scale;

    /**
     * @var float
     */
    protected $decay;

    /**
     * @var float|int|null
     */
    protected $offset;

    /**
     * @var string
     */
    protected $decayFunction = 'exp';

    /**
     * @param string $field
     * @param string $scale
     * @param float $decay
     * @param float|int|null $offset
     */
    public function __construct($field, $scale, $decay = 0.5, $offset = null)
    {
        $this->field = $field;
        $this->scale = $scale;
        $this->decay = $decay;
        $this->offset = $offset;
    }

    /**
     * @param QueryBuilderInterface $queryBuilder
     * @return array
     */
    public function apply(QueryBuilderInterface $queryBuilder)
    {
        $body = $this->wrap($queryBuilder);

        $body['query']['function_score']['functions'][] = [
            $this->decayFunction => [
                $this->field => $this->getSetup()
            ]
        ];

        return $body;
    }

    /**
     * @return array
     */
    private function getSetup()
    {
        $result = [
            'scale' => $this->scale,
            'decay' => $this->decay
        ];

        if ($this->offset) {
            $result['offset'] = $this->offset;
        }

        return $result;
    }
}
