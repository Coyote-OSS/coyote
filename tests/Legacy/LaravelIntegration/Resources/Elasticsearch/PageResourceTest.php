<?php

namespace Tests\Legacy\LaravelIntegration\Resources\Elasticsearch;

use Coyote\Forum;
use Coyote\Http\Resources\Elasticsearch\PageResource;
use Coyote\Microblog;
use Coyote\Page;
use Coyote\Topic;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Legacy\LaravelIntegration\TestCase;

class PageResourceTest extends TestCase
{
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        PageResource::withoutWrapping();
    }

    public function testMapTopicModel()
    {
        $title = $this->faker->text;
        $forum = factory(Forum::class)->make(['is_prohibited' => true]);
        /** @var Topic $topic */
        $topic = factory(Topic::class)->state('id')->make();

        $topic->forum()->associate($forum);

        $page = new Page(['title' => $title, 'path' => '/fake_path']);
        $page->content()->associate($topic);

        $result = (new PageResource($page))->toResponse($this->app['request'])->getData(true);

        $this->assertEquals($page->path, $result['path']);
        $this->assertEquals($page->title, $result['title']);
        $this->assertTrue($result['forum']['is_prohibited']);
    }

    public function testMapMicroblogModel()
    {
        $microblog = factory(Microblog::class)->state('id')->make();

        $page = new Page(['title' => $this->faker->text, 'path' => '/fake_path']);
        $page->content()->associate($microblog);

        $result = (new PageResource($page))->toResponse($this->app['request'])->getData(true);

        $this->assertEquals($page->path, $result['path']);
        $this->assertEquals($page->title, $result['title']);
        $this->assertArrayNotHasKey('forum', $result);
    }
}
