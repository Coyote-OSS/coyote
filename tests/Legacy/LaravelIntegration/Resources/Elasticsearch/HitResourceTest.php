<?php

namespace Tests\Legacy\LaravelIntegration\Resources\Elasticsearch;

use Coyote\Http\Resources\Elasticsearch\HitResource;
use Faker\Factory;
use Tests\Legacy\LaravelIntegration\TestCase;

class HitResourceTest extends TestCase
{
    public function testTopicHitWithPostChildren()
    {
        $faker = Factory::create();

        $hit = [
            'title' => $faker->text(100),
            'model' => 'Topic',
            'children' => [[
                'text' => $text = $faker->text
            ]]
        ];

        $result = (new HitResource($hit))->toArray($this->app['request']);

        $this->assertEmpty($result['children']);
        $this->assertEquals($text, $result['text']);
    }

    public function testMicroblogHitWithChildren()
    {
        $hit = [
            'text' => 'Lorem ipsum <em>searching text</em>',
            'model' => 'Microblog',
            'children' => [[
                'text' => 'Another <em>searching text</em> in children'
            ]]
        ];

        $result = (new HitResource($hit))->toArray($this->app['request']);

        $this->assertNotEmpty($result['children']);
        $this->assertEquals($hit['text'], $result['text']);
        $this->assertEquals($hit['children'][0]['text'], $result['children'][0]['text']);
    }

    public function testMicroblogHitWithChildrenAndNoHighlightedText()
    {
        $faker = Factory::create();

        $hit = [
            'text' => $faker->text,
            'model' => 'Microblog',
            'children' => [[
                'text' => 'Another <em>searching text</em> in children'
            ]]
        ];

        $result = (new HitResource($hit))->toArray($this->app['request']);

        $this->assertEmpty($result['children']);
        $this->assertEquals($hit['children'][0]['text'], $result['text']);
    }
}
