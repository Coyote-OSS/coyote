<?php

namespace Tests\Legacy\LaravelIntegration\Controllers\Forum;

use Coyote\Topic;
use Coyote\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Legacy\LaravelIntegration\TestCase;

class VoteControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function testShowVoters()
    {
        /** @var Topic $topic */
        $topic = factory(Topic::class)->create();

        $users = factory(User::class, 5)->create()->each(function ($user) use ($topic) {
            $topic->firstPost->votes()->create(['user_id' => $user->id, 'forum_id' => $topic->forum_id]);
        });

        $response = $this->get('/Forum/Post/Voters/' . $topic->firstPost->id);

        $response->assertJson(['id' => $topic->firstPost->id, 'users' => $users->pluck('name')->toArray()]);
    }
}
