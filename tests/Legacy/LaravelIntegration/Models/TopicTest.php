<?php

namespace Tests\Legacy\LaravelIntegration\Models;

use Coyote\Forum;
use Coyote\Post;
use Coyote\Topic;
use Coyote\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Legacy\LaravelIntegration\TestCase;

class TopicTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @var Forum
     */
    private $forum;

    /**
     * @var User
     */
    private $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->forum = factory(Forum::class)->create();
        $this->user = factory(User::class)->create();
    }

    public function testTopicCreationAsAnonymousUser()
    {
        $this->assertEquals(0, $this->forum->topics);

        $topic = factory(Topic::class)->create(['forum_id' => $this->forum->id]);
        $this->forum->refresh();

        $this->assertEquals(1, $this->forum->topics);
        $this->assertEquals(0, $topic->replies);
    }

    public function testTopicCreationAsRegisteredUser()
    {
        $topic = factory(Topic::class)->create(['forum_id' => $this->forum->id]);
        factory(Post::class)->create(['forum_id' => $this->forum->id, 'topic_id' => $topic->id, 'user_id' => $this->user->id]);

        $this->user->refresh();

        $this->assertEquals(1, $this->user->posts);
    }

    public function testTopicDelete()
    {
        $topic = factory(Topic::class)->create(['forum_id' => $this->forum->id]);
        $topic->forceDelete();

        $this->forum->refresh();

        $this->assertEquals(0, $this->forum->topics);
        $this->assertEquals(null, $this->forum->last_post_id);

        $this->assertDatabaseMissing('posts', ['forum_id' => $this->forum->id]);
    }

    public function testTopicSoftDelete()
    {
        $topic = factory(Topic::class)->create(['forum_id' => $this->forum->id]);
        factory(Post::class)->create(['forum_id' => $this->forum->id, 'topic_id' => $topic->id, 'user_id' => $this->user->id]);

        $this->user->refresh();
        $this->assertEquals(1, $this->user->posts);

        $topic->delete();

        $this->assertDatabaseHas('topics', ['forum_id' => $topic->forum_id]);

        $this->assertEquals(0, $topic['replies']);
        $this->assertEquals(0, $topic['replies_real']);

        $this->forum->refresh();

        $this->assertEquals(0, $this->forum->topics);
        $this->assertEquals(0, $this->forum->posts);
        $this->assertEquals(null, $this->forum->last_post_id);

        $this->assertDatabaseHas('posts', ['forum_id' => $this->forum->id]);

        $this->user->refresh();
        $this->assertEquals(0, $this->user->posts);
    }

    public function testTopicRestore()
    {
        $topic = factory(Topic::class)->create(['forum_id' => $this->forum->id]);
        $this->forum->refresh();

        $before = clone $this->forum;

        $topic->delete();
        $topic->restore();

        $this->forum->refresh();

        $this->assertEquals($before->topics, $this->forum->topics);
        $this->assertEquals(1, $this->forum->posts);

        $this->assertDatabaseHas('posts', ['forum_id' => $this->forum->id, 'deleted_at' => null]);

        $this->forum->refresh();

        $this->assertEquals(1, $this->forum->topics);
        $this->assertEquals(1, $this->forum->posts);
        $this->assertNotNull($this->forum->last_post_id);
    }

    public function testTopicMove()
    {
        $topic = factory(Topic::class)->create(['forum_id' => $this->forum->id]);
        $this->forum->refresh();

        $this->assertEquals(1, $this->forum->topics);

        $newForum = factory(Forum::class)->create();

        $topic->forum_id = $newForum->id;
        $topic->save();

        $newForum->refresh();

        $this->assertEquals(1, $newForum->topics);
        $this->assertDatabaseMissing('posts', ['forum_id' => $this->forum->id]);
    }
}
