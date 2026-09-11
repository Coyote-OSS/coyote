<?php

namespace Tests\Legacy\LaravelIntegration\Policies;

use Coyote\Forum;
use Coyote\Policies\PostPolicy;
use Coyote\Post;
use Coyote\Reputation;
use Coyote\Topic;
use Coyote\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Gate;
use Tests\Legacy\LaravelIntegration\TestCase;

class PostPolicyTest extends TestCase
{
    use WithFaker;

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

    public function setUp(): void
    {
        parent::setUp();

        $this->forum = factory(Forum::class)->make();
        $this->topic = factory(Topic::class)->make(['forum_id' => $this->forum->id]);

        $this->user = factory(User::class)->make(['id' => $this->faker->numberBetween()]);

        /** @var Post $post */
        $this->post = factory(Post::class)->make(['id' => $this->faker->numberBetween(), 'user_id' => $this->user->id]);

        $this->post->topic()->associate($this->topic);
        $this->post->forum()->associate($this->forum);
    }

    public function testDeleteAndRestoreAllowedDueToLastPostInTopic()
    {
        $this->topic->last_post_id = $this->post->id; // last post in topic

        $this->assertEquals($this->topic->last_post_id, $this->post->id);

        $policy = new PostPolicy();
        $this->assertTrue($policy->delete($this->user, $this->post));

        $this->post->deleted_at = now();

        $this->assertTrue($policy->delete($this->user, $this->post));
    }

    public function testDeleteAndRestoreAllowedDespiteAllowedTime()
    {
        $this->topic->last_post_id = $this->post->id; // last post in topic

        $this->post->created_at = now()->subMinutes(31);

        $policy = new PostPolicy();
        $this->assertTrue($policy->delete($this->user, $this->post));
    }

    public function testDeleteAndRestoreNotAllowedBecauseLaterAnswers()
    {
        $post = factory(Post::class)->make(['id' => $this->faker->numberBetween()]);
        $this->topic->last_post_id = $post->id; // last post in topic

        $policy = new PostPolicy();
        $this->assertFalse($policy->delete($this->user, $this->post));

        $this->post->deleted_at = now();

        $this->assertFalse($policy->delete($this->user, $this->post));
    }

    public function testDeleteNotAllowedDueToLockedTopic()
    {
        $this->post->topic->is_locked = true;

        $policy = new PostPolicy();
        $this->assertFalse($policy->delete($this->user, $this->post));
    }

    public function testDeleteIsAllowedInTopicByAdmin()
    {
        $this->post->topic->is_locked = true;
        $this->post->created_at = now()->subMonths(2);

        Gate::define('forum-delete', function () {
            return true;
        });

        $policy = new PostPolicy();
        $this->assertTrue($policy->delete($this->user, $this->post));
    }

    public function testDeleteIsAllowedInForumByAdmin()
    {
        $this->post->forum->is_locked = true;

        Gate::define('forum-delete', function () {
            return true;
        });

        $policy = new PostPolicy();
        $this->assertTrue($policy->delete($this->user, $this->post));
    }

    public function testUpdateAllowedDueToLastPostInTopic()
    {
        $policy = new PostPolicy();
        $this->assertTrue($policy->update($this->user, $this->post));
    }

    public function testUpdateNotAllowedDueToTimeOfCreation()
    {
        $this->post->created_at = now()->subHours(25);

        $policy = new PostPolicy();
        $this->assertFalse($policy->update($this->user, $this->post));
    }

    public function testUpdateAllowedDueToHighReputation()
    {
        // new post in topic
        $post = factory(Post::class)->make(['id' => $this->faker->numberBetween()]);
        $this->topic->last_post_id = $post->id; // last post in topic

        $post->setRelation('topic', $this->topic);

        // change date of old post
        $this->post->created_at = now()->subMinutes(31);

        $this->assertEquals($this->topic->last_post_id, $post->id);

        $this->user->reputation = Reputation::EDIT_POST;

        $policy = new PostPolicy();
        $this->assertTrue($policy->update($this->user, $this->post));
    }

    public function testUpdateAllowedDueToTimeOfCreation()
    {
        $policy = new PostPolicy();
        $this->assertTrue($policy->update($this->user, $this->post));
    }

    public function testUpdateNotAllowedDueToArchive()
    {
        $this->post->created_at = now()->subMonth();

        $policy = new PostPolicy();
        $this->assertFalse($policy->update($this->user, $this->post));
    }

    public function testDeleteNotAllowedDueToArchive()
    {
        $this->post->created_at = now()->subMonth();

        $policy = new PostPolicy();
        $this->assertFalse($policy->delete($this->user, $this->post));
    }
}
