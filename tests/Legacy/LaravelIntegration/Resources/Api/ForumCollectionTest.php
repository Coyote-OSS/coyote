<?php

namespace Tests\Legacy\LaravelIntegration\Resources\Api;

use Coyote\Forum;
use Coyote\Http\Resources\ForumCollection;
use Coyote\Repositories\Contracts\ForumRepositoryInterface;
use Coyote\Repositories\Contracts\TopicRepositoryInterface;
use Coyote\Repositories\Criteria\Forum\AccordingToUserOrder;
use Coyote\Services\Forum\Tracker;
use Coyote\Services\Guest;
use Coyote\Topic;
use Faker;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Legacy\LaravelIntegration\TestCase;

class ForumCollectionTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @var ForumRepositoryInterface
     */
    private $repository;

    public function setUp(): void
    {
        parent::setUp();

        $this->repository = app(ForumRepositoryInterface::class);
    }

    public function testCollectForumWithChildren()
    {
        $parent = factory(Forum::class)->create();
        $child2 = factory(Forum::class)->create(['parent_id' => $parent->id, 'order' => 2]);
        $child1 = factory(Forum::class)->create(['parent_id' => $parent->id, 'order' => 1]);

        $this->createTopic($child2->id);

        $child1->refresh();
        $child2->refresh();

        $this->assertEquals(1, $child1->order);
        $this->assertEquals(2, $child2->order);

        $faker = Faker\Factory::create();

        $guestId = $faker->uuid;

        $this->repository->pushCriteria(new AccordingToUserOrder(null));
        $forums = $this->repository->categories($guestId)->mapCategory();

        $result = collect($forums)->keyBy('id');

        $this->assertEquals(1, $result[$child1->id]['order']);
        $this->assertEquals(2, $result[$child2->id]['order']);

        $collection = new ForumCollection($forums);

        $result = $collection->toArray(request());

        $result = collect($result)->keyBy('id');

        $this->assertTrue(isset($result[$parent->id]));
        $this->assertTrue(isset($result[$parent->id]['children']));

        $children = $result[$parent->id]['children'];

        $this->assertEquals(2, count($children));
        $this->assertEquals(1, $children[0]['order']);
        $this->assertEquals(2, $children[1]['order']);

        $this->assertEquals($child1->name, $children[0]['name']);
        $this->assertEquals($child2->name, $children[1]['name']);

        $this->assertEquals(1, $result[$parent->id]['topics']);
        $this->assertEquals(1, $result[$parent->id]['posts']);

        $this->assertEquals(1, $children[1]['topics']);
        $this->assertEquals(1, $children[1]['posts']);
    }

    public function testCategoryUnreadForNewUser()
    {
        $parent = factory(Forum::class)->create();

        $this->assertEquals(0, $parent->topics);
        $this->assertEquals(0, $parent->posts);

        $faker = Faker\Factory::create();

        $guestId = $faker->uuid;
        $result = $this->getCategories($guestId);

        $this->assertTrue($result[$parent->id]['is_read']);

        $topic = factory(Topic::class)->create(['forum_id' => $parent->id]);
        $topic->refresh();

        $guest = new Guest($guestId);
        $guest->setDefaultSessionTime(now()->subMinutes(5)); // simulate session start

        $tracker = new Tracker($topic, $guest);

        $this->assertFalse($tracker->isRead());

        $result = $this->getCategories($guestId, $guest);

        $this->assertFalse($result[$parent->id]['is_read']);
    }

    public function testCategoryUnreadWithGuest()
    {
        $faker = Faker\Factory::create();

        $guestId = $faker->uuid;
        $now = now()->subMinutes(5);

        \Coyote\Guest::forceCreate(['id' => $guestId, 'created_at' => $now, 'updated_at' => $now]);

        $parent = factory(Forum::class)->create();

        $result = $this->getCategories($guestId);
        $this->assertTrue($result[$parent->id]['is_read']);

        $topic = factory(Topic::class)->create(['forum_id' => $parent->id]);
        $topic->refresh();

        $guest = new Guest($guestId);

        $tracker = new Tracker($topic, $guest);

        $this->assertFalse($tracker->isRead());

        $result = $this->getCategories($guestId, $guest);

        $this->assertFalse($result[$parent->id]['is_read']);
    }

    public function testCategoryUnreadWithChildren()
    {
        $parent = factory(Forum::class)->create();
        $child1 = factory(Forum::class)->create(['parent_id' => $parent->id]);

        $parentTopic = $this->createTopic($parent->id);
        $childTopic = $this->createTopic($child1->id);

        $parent->refresh();
        $child1->refresh();

        $faker = Faker\Factory::create();

        $guestId = $faker->uuid;
        $now = now()->subMinutes(5);

        \Coyote\Guest::forceCreate(['id' => $guestId, 'created_at' => $now, 'updated_at' => $now]);

        $guest = new Guest($guestId);

        $tracker = new Tracker($childTopic, $guest);
        $tracker->setRepository(app(TopicRepositoryInterface::class));
        $tracker->asRead($childTopic->last_post_created_at);

        $result = $this->getCategories($guestId, $guest);

        $this->assertFalse($result[$parent->id]['is_read']);
        $this->assertTrue($result[$parent->id]['children'][0]['is_read']);

        $tracker = new Tracker($parentTopic, $guest);
        $tracker->setRepository(app(TopicRepositoryInterface::class));
        $tracker->asRead($parentTopic->last_post_created_at);

        $result = $this->getCategories($guestId, $guest);

        $this->assertTrue($result[$parent->id]['is_read']);
        $this->assertTrue($result[$parent->id]['children'][0]['is_read']);
    }

    private function getCategories($guestId, $guest = null)
    {
        $this->repository->pushCriteria(new AccordingToUserOrder(null));
        $forums = $this->repository->categories($guestId)->mapCategory();

        $collection = (new ForumCollection($forums))->setGuest($guest);
        $result = $collection->toArray(request());

        $this->repository->resetCriteria();

        return collect($result)->keyBy('id');
    }
}
