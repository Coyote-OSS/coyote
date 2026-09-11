<?php

namespace Tests\Legacy\LaravelIntegration\Services;

use Carbon\Carbon;
use Coyote\Forum;
use Coyote\Guest;
use Coyote\Http\Resources\ForumResource;
use Coyote\Post;
use Coyote\Repositories\Contracts\TopicRepositoryInterface;
use Coyote\Services\Forum\Tracker;
use Coyote\Topic;
use Faker;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Legacy\LaravelIntegration\TestCase;

class TrackerTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @var Forum
     */
    private $forum;
    private $guestId;
    private $faker;

    public function setUp(): void
    {
        parent::setUp();

        $this->faker = Faker\Factory::create();

        $this->forum = factory(Forum::class)->create();
        $this->guestId = $this->faker->uuid;

        $this->app['request']->setLaravelSession(app('session.store'));

        session()->put('created_at', Carbon::now()->timestamp);
    }

    public function testIsReadWithOldTopic()
    {
        $topic = factory(Topic::class)->create(['forum_id' => $this->forum->id]);
        factory(Post::class)->create(['forum_id' => $this->forum->id, 'topic_id' => $topic->id, 'created_at' => Carbon::now()->subMinute(5)]);

        $topic->refresh(); // refresh from db after submitting new post

        $marker = $this->factory($topic, $this->faker->uuid);

        // post is NOT new
        $this->assertTrue($marker->isRead());
    }

    public function testIsReadWithNewlyRegisteredUser()
    {
        $topic = factory(Topic::class)->create(['forum_id' => $this->forum->id]);
        factory(Post::class)->create(['forum_id' => $this->forum->id, 'topic_id' => $topic->id, 'created_at' => Carbon::now()->subMinute(5)]);

        $topic->refresh(); // refresh from db after submitting new post

        $marker = $this->factory($topic, null);

        // post is NOT new
        $this->assertTrue($marker->isRead());

        Guest::forceCreate(['id' => $this->guestId, 'created_at' => now(), 'updated_at' => now()]);

        factory(Post::class)->create(['forum_id' => $this->forum->id, 'topic_id' => $topic->id, 'created_at' => Carbon::now()->addMinute(1)]);
        $topic->refresh(); // refresh from db after submitting new post

        $marker = $this->factory($topic, $this->guestId);

        // is post new
        $this->assertFalse($marker->isRead());
    }

    public function testMarkAsReadWithOnlyOneTopic()
    {
        $topic = factory(Topic::class)->create(['forum_id' => $this->forum->id]);
        factory(Post::class)->create(['forum_id' => $this->forum->id, 'topic_id' => $topic->id, 'created_at' => Carbon::now()->subMinute(1)]);

        $topic->refresh(); // refresh from db after submitting new post

        $tracker = $this->factory($topic, $this->guestId);
        $tracker->asRead($topic->last_post_created_at);

        $this->assertDatabaseHas('forum_track', ['forum_id' => $this->forum->id, 'guest_id' => $this->guestId]);
    }

    public function testMarkAsReadWithMultipleTopics()
    {
        Guest::create(['id' => $this->guestId, 'updated_at' => $now = now()]);

        $topic1 = factory(Topic::class)->create(['forum_id' => $this->forum->id]);
        factory(Post::class)->create(['forum_id' => $this->forum->id, 'topic_id' => $topic1->id, 'created_at' => Carbon::now()->addMinute(2)]);

        $topic1->refresh(); // refresh from db after submitting new post

        $topic2 = factory(Topic::class)->create(['forum_id' => $this->forum->id]);
        factory(Post::class)->create(['forum_id' => $this->forum->id, 'topic_id' => $topic2->id, 'created_at' => Carbon::now()->addMinute(1)]);

        $topic2->refresh(); // refresh from db after submitting new post

        $tracker = $this->factory($topic2, $this->guestId);
        $this->assertFalse($tracker->isRead());

        $tracker->asRead($topic2->last_post_created_at);

        $this->assertTrue($tracker->isRead());

        // new post in topic
        factory(Post::class)->create(['topic_id' => $topic2->id, 'forum_id' => $this->forum->id, 'created_at' => Carbon::now()->addSeconds(65)]);
        $topic2->refresh(); // refresh from db after submitting new post

        $tracker = $this->factory($topic2, $this->guestId);
        $this->assertFalse($tracker->isRead());

        $tracker->asRead($topic2->last_post_created_at);

        $tracker = $this->factory($topic1, $this->guestId);

        $this->assertFalse($tracker->isRead());

        $this->assertDatabaseHas('topic_track', ['forum_id' => $this->forum->id, 'topic_id' => $topic2->id, 'guest_id' => $this->guestId]);
        $this->assertDatabaseMissing('topic_track', ['forum_id' => $this->forum->id, 'topic_id' => $topic1->id, 'guest_id' => $this->guestId]);

        $this->forum->refresh();
        $this->forum->read_at = $now;

        $this->forum->load('post.topic');
        $this->forum->post->setRelation('topic', $this->factory($this->forum->post->topic, $this->guestId));

        $resource = new ForumResource($this->forum);
        $forum = $resource->toArray($this->app['request']);

        $this->assertFalse($forum['is_read']);
        $this->assertTrue($forum[0]->data['topic']['is_read']);
    }

    public function testMarkAsReadWithDeletedTopics()
    {
        $topic = factory(Topic::class)->create(['forum_id' => $this->forum->id, 'deleted_at' => Carbon::now()]);
        factory(Post::class)->create(['forum_id' => $this->forum->id, 'topic_id' => $topic->id, 'created_at' => Carbon::now()->subMinute(1), 'deleted_at' => Carbon::now()]);

        $topic->refresh(); // refresh from db after submitting new post

        $topic = factory(Topic::class)->create(['forum_id' => $this->forum->id]);
        factory(Post::class)->create(['forum_id' => $this->forum->id, 'topic_id' => $topic->id, 'created_at' => Carbon::now()->subMinute(1)]);

        $topic->refresh(); // refresh from db after submitting new post

        $tracker = $this->factory($topic, $this->guestId);
        $tracker->asRead($topic->last_post_created_at);

        $this->assertDatabaseHas('forum_track', ['forum_id' => $this->forum->id, 'guest_id' => $this->guestId]);
    }

    private function factory(Topic $model, ?string $guestId)
    {
        $guest = new \Coyote\Services\Guest($guestId);

        return (new Tracker($model, $guest))->setRepository(app(TopicRepositoryInterface::class));
    }
}
