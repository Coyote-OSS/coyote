<?php

namespace Tests\Legacy\LaravelIntegration\Policies;

use Coyote\Forum;
use Coyote\Policies\PostCommentPolicy;
use Coyote\Post;
use Coyote\Post\Comment;
use Coyote\Topic;
use Coyote\User;
use Faker\Factory;
use Illuminate\Support\Facades\Gate;
use Tests\Legacy\LaravelIntegration\TestCase;

class PostCommentPolicyTest extends TestCase
{
    /**
     * @var Forum
     */
    protected $forum;

    /**
     * @var User
     */
    protected $user;

    /**
     * @var Comment
     */
    protected $comment;

    /**
     * @var \Faker\Generator
     */
    protected $faker;

    public function setUp(): void
    {
        parent::setUp();

        $this->faker = Factory::create();

        $this->forum = factory(Forum::class)->make();
        $topic = factory(Topic::class)->make();
        $this->user = factory(User::class)->make(['id' => $this->faker->numberBetween()]);

        $post = factory(Post::class)->make();
        $post->forum()->associate($this->forum);
        $post->topic()->associate($topic);

        $this->comment = new Comment();
        $this->comment->post()->associate($post);
    }

    public function testWriteInLockedForumIsNotAllowed()
    {
        $this->forum->is_locked = true;

        $policy = new PostCommentPolicy();
        $this->assertFalse($policy->write($this->user, $this->comment));
    }

    public function testWriteAsAdminInLockedForumIsAllowed()
    {
        $this->forum->is_locked = true;

        Gate::define('forum-update', function () {
            return true;
        });

        $policy = new PostCommentPolicy();
        $this->assertTrue($policy->write($this->user, $this->comment));
    }

    public function testDeleteIsNotAllowed()
    {
        $policy = new PostCommentPolicy();
        $this->assertFalse($policy->delete($this->user, $this->comment, $this->comment->post->forum));
    }

    public function testDeleteIsAllowedForAuthor()
    {
        $this->comment->user_id = $this->user->id;

        $policy = new PostCommentPolicy();
        $this->assertTrue($policy->delete($this->user, $this->comment, $this->comment->post->forum));
    }

    public function testDeleteIsAllowedForAdmin()
    {
        Gate::define('forum-delete', function () {
            return true;
        });

        $policy = new PostCommentPolicy();
        $this->assertTrue($policy->delete($this->user, $this->comment, $this->comment->post->forum));
    }
}
