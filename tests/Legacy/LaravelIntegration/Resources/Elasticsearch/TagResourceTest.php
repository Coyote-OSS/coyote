<?php

namespace Tests\Legacy\LaravelIntegration\Resources\Elasticsearch;

use Coyote\Http\Resources\Elasticsearch\TagResource;
use Coyote\Job;
use Coyote\Microblog;
use Coyote\Tag;
use Coyote\Topic;
use Tests\Legacy\LaravelIntegration\TestCase;

class TagResourceTest extends TestCase
{
    public function testMapModel()
    {
        $tag = factory(Tag::class)->make(['resources' => [Topic::class => 1, Microblog::class => 2, Job::class => 3]]);

        TagResource::withoutWrapping();

        $resource = TagResource::make($tag)->toResponse(request())->getData(true);

        $this->assertEquals($tag->name, $resource['name']);
        $this->assertEquals($tag->real_name, $resource['real_name']);
        $this->assertEquals($tag->resources[Topic::class], $resource['topics']);
        $this->assertEquals($tag->resources[Microblog::class], $resource['microblogs']);
        $this->assertEmpty($resource['logo']);
    }
}
