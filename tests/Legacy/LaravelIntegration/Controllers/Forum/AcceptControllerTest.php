<?php

namespace Tests\Legacy\LaravelIntegration\Controllers\Forum;

use Coyote\Forum;
use Coyote\Post;
use Coyote\Topic;
use Coyote\User;
use Faker\Factory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Gate;
use Tests\Legacy\LaravelIntegration\TestCase;

class AcceptControllerTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @var Forum
     */
    private $forum;

    /**
     * @var Topic
     */
    private $topic;

    /**
     * @var Post
     */
    private $post;

    /**
     * @var User
     */
    private $user;

    /**
     * @var \Faker\Generator
     */
    protected $faker;

    public function setUp(): void
    {
        parent::setUp();

        $this->faker = Factory::create();

        $this->forum = factory(Forum::class)->create();
        $this->user = $this->createUserWithGroup();

        $this->topic = factory(Topic::class)->create(['forum_id' => $this->forum->id]);

        $this->post = factory(Post::class)->create(['forum_id' => $this->forum->id, 'topic_id' => $this->topic->id]);

        $this->topic->refresh();
    }

    public function testAcceptPostInLockedTopic()
    {
        $this->topic->is_locked = true;
        $this->topic->save();

        $response = $this->actingAs($this->user)->json('POST', "/Forum/Post/Accept/{$this->post->id}");

        $response->assertStatus(403);
        $response->assertJsonFragment(['message' => 'Wątek jest zablokowany.']);
    }

    public function testAcceptPostInLockedForum()
    {
        $this->forum->is_locked = true;
        $this->forum->save();

        $response = $this->actingAs($this->user)->json('POST', "/Forum/Post/Accept/{$this->post->id}");

        $response->assertStatus(403);
        $response->assertJsonFragment(['message' => 'Forum jest zablokowane.']);
    }

    public function testAcceptPostInTopicIsNotAllowed()
    {
        $response = $this->actingAs($this->user)->json('POST', "/Forum/Post/Accept/{$this->post->id}");

        $response->assertStatus(403);
        $response->assertJsonFragment(['message' => 'Możesz zaakceptować post tylko we własnym wątku.']);
    }

    public function testAcceptFirstPostInTopicNotAllowed()
    {
        Gate::allows('forum-update', function () {
            return true;
        });

        $this->topic->firstPost->user_id = $this->user->id;
        $this->topic->firstPost->save();

        $postId = $this->topic->firstPost->id;

        $response = $this->actingAs($this->user)->json('POST', "/Forum/Post/Accept/{$postId}");

        $response->assertStatus(500);
        $response->assertJsonFragment(['message' => 'Nie można zaakceptować pierwszego posta w wątku.']);
    }
}
