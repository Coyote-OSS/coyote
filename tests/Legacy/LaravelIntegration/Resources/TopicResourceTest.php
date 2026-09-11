<?php

namespace Tests\Legacy\LaravelIntegration\Resources;

use Coyote\Http\Resources\TopicCollection;
use Coyote\Http\Resources\TopicResource;
use Coyote\Repositories\Contracts\TopicRepositoryInterface;
use Coyote\Services\Forum\Tracker;
use Coyote\Services\Guest;
use Coyote\Tag;
use Coyote\Topic;
use Coyote\User;
use Faker\Factory;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\Legacy\LaravelIntegration\TestCase;

class TopicResourceTest extends TestCase
{
    private $repository;
    private $guest;

    public function setUp(): void
    {
        parent::setUp();

        $faker = Factory::create();

        $this->repository = $this->app[TopicRepositoryInterface::class];
        $this->guest = new Guest($faker->uuid);
    }

    public function testTransformPaginatorToJson()
    {
        $topic = factory(Topic::class)->state('id')->make();
        $paginator = new LengthAwarePaginator([$topic], 1, 10);

        TopicCollection::wrap('data');

        $collection = (new TopicCollection($paginator))
            ->setGuest($this->guest)
            ->setRepository($this->repository);

        $result = $collection->toResponse(request())->getData(true);

        $this->assertEquals(1, $result['meta']['total']);
        $this->assertEquals(1, $result['meta']['current_page']);
        $this->assertEquals($topic['title'], $result['data'][0]['title']);
        $this->assertArrayNotHasKey('tags', $result);
    }

    public function testTransformResource()
    {
        $user = factory(User::class)->state('id')->make();

        /** @var Topic $topic */
        $topic = factory(Topic::class)->state('id')->make();
        $topic->firstPost->user_id = $user->id;

        $tag = factory(Tag::class)->make();
        $topic->setRelation('tags', collect([$tag]));

        $this->assertTrue($topic->isRelation('firstPost'));
        $this->assertTrue($topic->isRelation('lastPost'));
        $this->assertTrue($topic->isRelation('tags'));

        TopicResource::withoutWrapping();

        $tracker = new Tracker($topic, $this->guest);
        $result = (new TopicResource($tracker))->toResponse(request())->getData(true);

        $this->assertArrayHasKey('owner_id', $result);
        $this->assertArrayHasKey('last_post', $result);
        $this->assertArrayHasKey('tags', $result);

        $this->assertEquals($user->id, $result['owner_id']);
        $this->assertEquals($tag->name, $result['tags'][0]['name']);
        $this->assertEquals($tag->real_name, $result['tags'][0]['real_name']);

        $topic->unsetRelation('firstPost');
        $topic->unsetRelation('lastPost');

        $tracker = new Tracker($topic, $this->guest);
        $result = (new TopicResource($tracker))->toResponse(request())->getData(true);

        $this->assertArrayNotHasKey('firstPost', $result);
        $this->assertArrayNotHasKey('lastPost', $result);
    }
}
