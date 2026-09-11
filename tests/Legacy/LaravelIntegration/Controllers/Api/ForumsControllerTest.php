<?php

namespace Tests\Legacy\LaravelIntegration\Controllers\Api;

use Coyote\Forum;
use Coyote\Guest;
use Coyote\Topic;
use Coyote\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Legacy\LaravelIntegration\TestCase;

class ForumsControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function testShowAllAccessCategoriesAsAnonymousUserWithEmptyCategory()
    {
        $forum = factory(Forum::class)->create(['order' => 0]);

        $request = $this->get('/v1/forums', ['Accept' => 'application/json']);
        $data = $request->decodeResponseJson();

        $this->assertEquals($forum->name, $data[0]['name']);
        $this->assertTrue($data[0]['is_read']);
    }

    public function testShowAllAccessCategoriesAsAnonymousUserWithNonEmptyCategory()
    {
        $forum = factory(Forum::class)->create(['order' => 0]);
        factory(Topic::class)->create(['forum_id' => $forum->id]);

        $request = $this->get('/v1/forums', ['Accept' => 'application/json']);
        $data = $request->decodeResponseJson();

        $this->assertEquals($forum->name, $data[0]['name']);
        $this->assertTrue($data[0]['is_read']);
    }

    public function testDoNotShowRestrictedCategories()
    {
        $user = $this->createUserWithGroup();

        $restricted = factory(Forum::class)->create(['order' => 0, 'is_prohibited' => true]);
        $restricted->access()->create(['group_id' => $user->groups()->first()->id]);

        factory(Forum::class)->create(['order' => 1]);

        $request = $this->get('/v1/forums', ['Accept' => 'application/json']);
        $data = $request->decodeResponseJson();

        $this->assertNotEquals($restricted->name, $data[0]['name']);
    }

    public function testShowCategoryAsUnread()
    {
        $user = factory(User::class)->create();

        Guest::forceCreate(['id' => $user->guest_id, 'created_at' => now()->subMinutes(5)]);

        $forum = factory(Forum::class)->create(['order' => 0]);
        $topic = factory(Topic::class)->create(['forum_id' => $forum->id]);

        $token = $user->createToken('4programmers.net')->accessToken;

        $request = $this->get('/v1/forums', ['Accept' => 'application/json', 'Authorization' => 'Bearer ' . $token]);
        $data = $request->decodeResponseJson();

        $this->assertEquals($forum->name, $data[0]['name']);
        $this->assertEquals($topic->title, $data[0]['topic']['subject']);
        $this->assertFalse($data[0]['topic']['is_read']);
        $this->assertFalse($data[0]['is_read']);
    }

    public function testMarkCategoryAsRead()
    {
        $user = factory(User::class)->create();
        $token = $user->createToken('4programmers.net')->accessToken;

        Guest::forceCreate(['id' => $user->guest_id, 'created_at' => now()->subMinutes(5)]);

        $forum = factory(Forum::class)->create(['order' => 0]);
        $topic = factory(Topic::class)->create(['forum_id' => $forum->id]);

        Forum\Track::forceCreate(['forum_id' => $forum->id, 'guest_id' => $user->guest_id, 'marked_at' => $topic->last_post_created_at]);

        $request = $this->get('/v1/forums', ['Accept' => 'application/json', 'Authorization' => 'Bearer ' . $token]);
        $data = $request->decodeResponseJson();

        $this->assertTrue($data[0]['topic']['is_read']);
        $this->assertTrue($data[0]['is_read']);
    }
}
