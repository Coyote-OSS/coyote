<?php

namespace Tests\Legacy\LaravelIntegration\Controllers\Forum;

use Coyote\Forum;
use Coyote\Http\Middleware\ThrottleSubmission;
use Coyote\Permission;
use Coyote\Post;
use Coyote\Topic;
use Coyote\User;
use Faker\Factory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Tests\Legacy\LaravelIntegration\TestCase;

class SubmitControllerTest extends TestCase
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
        $this->user = $this->createUserWithGroup();

        $this->withoutMiddleware([
            ThrottleRequests::class,
            ThrottleSubmission::class,
            'throttle.submission'
        ]);
    }

    public function testSubmitWithInvalidTags()
    {
        $this->forum->require_tag = true;
        $this->forum->save();

        $response = $this->actingAs($this->user)->json('POST', "/Forum/{$this->forum->slug}/Submit");
        $response->assertJsonValidationErrors(['title', 'text', 'tags']);

        $response = $this->actingAs($this->user)->json(
            'POST',
            "/Forum/{$this->forum->slug}/Submit",
            ['tags' => ['aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa']]
        );

        $response->assertJsonValidationErrors(['tags']);

        $this->forum->require_tag = false;
        $this->forum->save();

        $response = $this->actingAs($this->user)->json('POST', "/Forum/{$this->forum->slug}/Submit");
        $response->assertJsonMissingValidationErrors(['tags']);
    }

    public function testSubmitTopicWithPost()
    {
        $post = factory(Post::class)->make();
        $faker = Factory::create();

        $response = $this->actingAs($this->user)->json(
            'POST',
            "/Forum/{$this->forum->slug}/Submit",
            ['text' => $post->text, 'title' => $faker->text(50), 'is_sticky' => true]
        );

        $response->assertJsonFragment([
            'text' => $post->text,
            'permissions' => [
                'delete' => true,
                'accept' => false,
                'update' => true,
                'write' => true
            ],
            'moderatorPermissions' => [
                'delete' => false,
                'update' => false,
                'accept' => false,
                'merge' => false,
                'sticky' => false,
                'admAccess' => false,
            ],
        ]);

        $id = $response->json('id');

        $this->assertDatabaseHas('posts', ['id' => $id]);
        $this->assertDatabaseHas('topics', ['first_post_id' => $id, 'is_sticky' => false]);

        /** @var Topic $topic */
        $topic = Topic::where('first_post_id', $id)->first();

        $this->assertTrue($topic->subscribers()->forUser($this->user->id)->exists());
        $this->assertTrue($topic->firstPost->subscribers()->forUser($this->user->id)->exists());
    }

    public function testSubmitStickyTopic()
    {
        $faker = Factory::create();

        $permission = Permission::where('name', 'forum-sticky')->get()->first();
        $group = $this->user->groups()->first();

        $this->forum->permissions()->create(['value' => 1, 'group_id' => $group->id, 'permission_id' => $permission->id]);

        $response = $this->actingAs($this->user)->json(
            'POST',
            "/Forum/{$this->forum->slug}/Submit",
            ['text' => $faker->text, 'title' => $faker->text(50), 'is_sticky' => true]
        );

        $id = $response->json('id');

        $this->assertDatabaseHas('posts', ['id' => $id]);
        $this->assertDatabaseHas('topics', ['first_post_id' => $id, 'is_sticky' => true]);
    }

    public function testSubmitPostToExistingTopicAndSubscribe()
    {
        $this->assertTrue($this->user->allow_subscribe);

        $topic = factory(Topic::class)->create(['forum_id' => $this->forum->id]);
        $post = factory(Post::class)->make();

        $response = $this->actingAs($this->user)->json('POST', "/Forum/{$this->forum->slug}/Submit/{$topic->id}", ['text' => $post->text]);

        $response->assertJsonFragment([
            'text' => $post->text,
            'is_read' => false,
            'is_locked' => false
        ]);

        $this->assertTrue($topic->subscribers()->forUser($this->user->id)->exists());

        $this->assertDatabaseHas('forum_track', ['forum_id' => $this->forum->id, 'guest_id' => $this->user->guest_id]);
    }

    public function testSubmitPostToExistingTopicAndDoNotSubscribe()
    {
        $this->user->allow_subscribe = false;
        $this->user->save();

        $this->assertFalse($this->user->allow_subscribe);

        $topic = factory(Topic::class)->create(['forum_id' => $this->forum->id]);
        $post = factory(Post::class)->make();

        $this->actingAs($this->user)->json('POST', "/Forum/{$this->forum->slug}/Submit/{$topic->id}", ['text' => $post->text]);

        $this->assertFalse($topic->subscribers()->forUser($this->user->id)->exists());

        $this->assertDatabaseHas('forum_track', ['forum_id' => $this->forum->id, 'guest_id' => $this->user->guest_id]);
    }

    public function testSubmitPostToExistingTopicWhereTagIsRequired()
    {
        $this->forum->require_tag = true;
        $this->forum->save();

        $topic = factory(Topic::class)->create(['forum_id' => $this->forum->id]);
        $post = factory(Post::class)->make();

        $response = $this->actingAs($this->user)->json('POST', "/Forum/{$this->forum->slug}/Submit/{$topic->id}", ['text' => $post->text]);

        $response->assertStatus(201);
    }

    public function testEditExistingPostByAuthor()
    {
        $faker = Factory::create();
        $topic = factory(Topic::class)->create(['forum_id' => $this->forum->id]);

        factory(Post::class)->create(['forum_id' => $this->forum->id, 'topic_id' => $topic->id]);
        $post = factory(Post::class)->create(['user_id' => $this->user->id, 'forum_id' => $this->forum->id, 'topic_id' => $topic->id]);

        $post->subscribe($this->user->id, true);

        $response = $this->actingAs($this->user)->json('POST', "/Forum/{$this->forum->slug}/Submit/{$topic->id}/{$post->id}", ['text' => $text = $faker->text]);

        $response->assertJsonFragment([
            'text' => $text
        ]);

        $post->refresh();
        $this->assertTrue($post->subscribers()->forUser($this->user->id)->exists());
    }

    public function testEditExistingPostByAnotherUser()
    {
        $faker = Factory::create();
        $topic = factory(Topic::class)->create(['forum_id' => $this->forum->id]);

        $post = factory(Post::class)->create(['forum_id' => $this->forum->id, 'topic_id' => $topic->id]);

        $response = $this->json('POST', "/Forum/{$this->forum->slug}/Submit/{$topic->id}/{$post->id}", ['text' => $faker->text]);
        $response->assertStatus(403);

        $response = $this->actingAs($this->user)->json('POST', "/Forum/{$this->forum->slug}/Submit/{$topic->id}/{$post->id}", ['text' => $faker->text]);
        $response->assertStatus(403);
    }

    public function testChangeTopicSubject()
    {
        $faker = Factory::create();
        /** @var Topic $topic */
        $topic = factory(Topic::class)->create(['forum_id' => $this->forum->id]);

        $post = $topic->posts()->first();

        $post->user_id = $this->user->id;
        $post->save();

        $response = $this->actingAs($this->user)->json(
            'POST',
            "/Forum/{$this->forum->slug}/Submit/{$topic->id}/{$post->id}",
            [
                'text' => $text = $faker->text,
                'title' => $subject = $faker->text(100),
                'poll' => [
                    'items' => [
                        ['text' => ''],
                        ['text' => '']
                    ],
                    'length' => 0,
                    'max_items' => 1
                ]
            ]
        );

        $response->assertJsonFragment([
            'text' => $text
        ]);

        $topic->refresh();

        $this->assertEquals($subject, $topic->title);
    }

    public function testFailToChangeTopicSubject()
    {
        $faker = Factory::create();
        $topic = factory(Topic::class)->create(['forum_id' => $this->forum->id]);

        factory(Post::class)->create(['forum_id' => $this->forum->id, 'topic_id' => $topic->id]);
        $post = factory(Post::class)->create(['user_id' => $this->user->id, 'forum_id' => $this->forum->id, 'topic_id' => $topic->id]);

        $this->actingAs($this->user)->json(
            'POST',
            "/Forum/{$this->forum->slug}/Submit/{$topic->id}/{$post->id}",
            [
                'text' => $faker->text,
                'title' => $subject = $faker->text(100)
            ]
        );

        $topic->refresh();

        $this->assertNotEquals($subject, $topic->title);
    }

    public function testEditExistingPostInLockedTopic()
    {
        $faker = Factory::create();
        $topic = factory(Topic::class)->create(['forum_id' => $this->forum->id, 'is_locked' => true]);

        $response = $this->actingAs($this->user)->json(
            'POST',
            "/Forum/{$this->forum->slug}/Submit/{$topic->id}/{$topic->first_post_id}",
            ['text' => $text = $faker->text]
        );

        $response->assertStatus(401);
    }

    public function testEditExistingPostInLockedForum()
    {
        $faker = Factory::create();
        $topic = factory(Topic::class)->create(['forum_id' => $this->forum->id]);

        $this->forum->is_locked = true;
        $this->forum->save();

        $response = $this->actingAs($this->user)->json(
            'POST',
            "/Forum/{$this->forum->slug}/Submit/{$topic->id}/{$topic->first_post_id}",
            ['text' => $text = $faker->text]
        );

        $response->assertStatus(401);
    }

    public function testSubmitTopicWithPoll()
    {
        $faker = Factory::create();

        $response = $this->actingAs($this->user)->json(
            'POST',
            "/Forum/{$this->forum->slug}/Submit",
            [
                'text' => $faker->text,
                'title' => $faker->text(50),
                'poll' => [
                    'max_items' => 1,
                    'length' => 0,
                    'items' => [
                        [
                            'text' => $itemA = $faker->realText(50)
                        ],
                        [
                            'text' => $itemB = $faker->realText(50)
                        ]
                    ]
                ]
            ]
        );

        $response->assertStatus(201);

        $id = $response->json('id');

        $topic = Topic::where('first_post_id', $id)->first();

        $this->assertNotNull($topic->poll_id);

        $this->assertDatabaseHas('poll_items', ['poll_id' => $topic->poll_id, 'text' => $itemA]);
        $this->assertDatabaseHas('poll_items', ['poll_id' => $topic->poll_id, 'text' => $itemB]);
    }

    public function testFailedToSubmitTopicWithOnePollAnswer()
    {
        $faker = Factory::create();
        $payload = [
            'text' => $faker->text,
            'title' => $faker->text(50),
            'poll' => [
                'max_items' => 1,
                'length' => 0,
                'items' => [
                    [
                        'text' => $itemA = $faker->realText(50)
                    ]
                ]
            ]
        ];

        $response = $this->actingAs($this->user)->json('POST', "/Forum/{$this->forum->slug}/Submit",  $payload);
        $response->assertStatus(422);

        array_push($payload['poll']['items'], ['text' => '']);

        $response = $this->actingAs($this->user)->json('POST', "/Forum/{$this->forum->slug}/Submit",  $payload);
        $response->assertStatus(422);
    }
}
