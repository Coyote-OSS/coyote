<?php

namespace Tests\Legacy\LaravelIntegration\Controllers\Forum;

use Coyote\Permission;
use Coyote\Post;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Legacy\LaravelIntegration\TestCase;

class MergeControllerTest extends TestCase
{
    use DatabaseTransactions;

    private $user;
    private $forum;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createUserWithGroup();
        $this->forum = $this->createForum([], $this->user->groups()->first()->id);
    }

    public function testMergeTwoPostWhenUnauthorized()
    {
        $topic = $this->createTopic($this->forum->id);
        $post = factory(Post::class)->create(['topic_id' => $topic->id, 'forum_id' => $this->forum->id]);

        $response = $this->json('POST', "/Forum/Post/Merge/$post->id");
        $response->assertStatus(403);
    }

    public function testMergeTwoPosts()
    {
        $this->forum->permissions()->create(['value' => 1, 'group_id' => $this->user->groups()->first()->id, 'permission_id' => Permission::where('name', 'forum-merge')->get()->first()->id]);

        $topic = $this->createTopic($this->forum->id);
        $post = factory(Post::class)->create(['topic_id' => $topic->id, 'forum_id' => $this->forum->id, 'created_at' => now()->addSecond()]);

        $topic->refresh();

        $original = $topic->firstPost->text;
        $response = $this->actingAs($this->user)->json('POST', "/Forum/Post/Merge/$post->id");

        $response->assertJsonFragment([
            'text' => $original . "\n\n" . $post->text
        ]);

        $post->refresh();

        $this->assertNotNull($post->deleted_at);
    }
}
