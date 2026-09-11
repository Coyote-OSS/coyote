<?php

namespace Tests\Legacy\LaravelIntegration\Controllers\Api;

use Coyote\Forum;
use Coyote\Group;
use Coyote\Post;
use Coyote\Topic;
use Coyote\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Legacy\LaravelIntegration\TestCase;

class PostsControllerTest extends TestCase
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

    /**
     * @var Topic
     */
    private $topic;

    /**
     * @var string
     */
    private $token;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = factory(User::class)->create();

        /** @var Group $group */
        $group = factory(Group::class)->create();
        $group->users()->attach($this->user->id);

        $this->forum = factory(Forum::class)->create(['is_prohibited' => true]);
        $this->forum->access()->create(['group_id' => $group->id]);
        $this->topic = factory(Topic::class)->create(['forum_id' => $this->forum->id]);

        $this->token = $this->user->createToken('4programmers.net')->accessToken;

        $this->topic->firstPost->comments()->save(factory(Post\Comment::class)->make(['user_id' => $this->user->id]));
        $this->topic->refresh();
    }

    public function testShowAllPosts()
    {
        $request = $this->get('/v1/posts', ['Accept' => 'application/json']);

        $data = $request->json('data');

        $this->assertNotEquals($data[0]['html'], $this->topic->firstPost->html);

        $this->assertArrayHasKey('id', $data[0]);
        $this->assertArrayHasKey('text', $data[0]);
        $this->assertArrayHasKey('html', $data[0]);
        $this->assertArrayHasKey('excerpt', $data[0]);
        $this->assertArrayHasKey('created_at', $data[0]);
        $this->assertArrayHasKey('url', $data[0]);
        $this->assertArrayHasKey('user', $data[0]);
        $this->assertArrayHasKey('edit_count', $data[0]);
    }

    public function testShowAllPostsAuthorized()
    {
        $request = $this->get('/v1/posts', ['Accept' => 'application/json', 'Authorization' => 'Bearer ' . $this->token]);
        $data = $request->json('data');

        $this->assertEquals($data[0]['html'], $this->topic->firstPost->html);
    }

    public function testShowForbiddenWhenUnauthorized()
    {
        $request = $this->get('/v1/posts/' . $this->topic->firstPost->id, ['Accept' => 'application/json']);

        $request->assertForbidden();
    }

    public function testShowPostWhenAuthorized()
    {
        $request = $this->get('/v1/posts/' . $this->topic->firstPost->id, ['Accept' => 'application/json', 'Authorization' => 'Bearer ' . $this->token]);

        $request->assertJsonFragment([
            'user_name' => $this->topic->firstPost->user_name,
            'topic_id' => $this->topic->id,
            'forum_id' => $this->forum->id,
            'text' => $this->topic->firstPost->text
        ]);

        $comment = array_first($request->json('comments'));

        $this->assertEquals($comment['text'], $this->topic->firstPost->comments->first()->text);
        $this->assertEquals($comment['id'], $this->topic->firstPost->comments->first()->id);
    }
}
