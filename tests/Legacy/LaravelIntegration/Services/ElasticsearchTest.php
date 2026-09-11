<?php

namespace Tests\Legacy\LaravelIntegration\Services;

use Tests\Legacy\LaravelIntegration\TestCase;

class ElasticsearchTest extends TestCase
{
    /**
     * @var \Elasticsearch\Client
     */
    private $client;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = app('elasticsearch');
    }

    public function testDashInText()
    {
        $result = $this->client->indices()->analyze([
            'index' => 'coyote',
            'body' => [
                "analyzer" => "stopwords_analyzer",
                "text" => "j2ee"
            ]
        ]);

        $this->assertEquals('j2ee', $result['tokens'][0]['token']);

        $result = $this->client->indices()->analyze([
            'index' => 'coyote',
            'body' => [
                "analyzer" => "stopwords_analyzer",
                "text" => "E-500"
            ]
        ]);

        $tokens = array_pluck($result['tokens'], 'token');

        $this->assertEquals($tokens[0], 'e-500');
        $this->assertEquals($tokens[1], 'e');
        $this->assertEquals($tokens[2], '500');

        $result = $this->client->indices()->analyze([
            'index' => 'coyote',
            'body' => [
                "analyzer" => "stopwords_analyzer",
                "text" => "wi-fi"
            ]
        ]);

        $tokens = array_pluck($result['tokens'], 'token');

        $this->assertEquals($tokens[0], 'wi-fi');
        $this->assertEquals($tokens[1], 'wi');
        $this->assertEquals($tokens[2], 'wifi');
        $this->assertEquals($tokens[3], 'fi');

        $result = $this->client->indices()->analyze([
            'index' => 'coyote',
            'body' => [
                "analyzer" => "stopwords_analyzer",
                "text" => "mark-walberg"
            ]
        ]);

        $tokens = array_pluck($result['tokens'], 'token');

        $this->assertEquals($tokens[0], 'mark-walberg');
        $this->assertEquals($tokens[1], 'mark');
        $this->assertEquals($tokens[2], 'markwalberg');
        $this->assertEquals($tokens[3], 'walberg');
    }
}
