<?php

namespace Tests\Legacy\LaravelIntegration\Controllers\Api;

use Coyote\Microblog;
use Coyote\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Legacy\LaravelIntegration\TestCase;

class MicroblogsControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function testPagination()
    {
        $microblog = factory(Microblog::class)->create();
        $this
            ->json('GET', '/v1/microblogs')
            ->assertStatus(200)
            ->assertSeeText($microblog->text);
    }

    public function testShowOneWithVotes()
    {
        /** @var Microblog $microblog */
        $microblog = factory(Microblog::class)->create(['votes' => 1]);
        $user = factory(User::class)->create();

        $microblog->voters()->create(['user_id' => $user->id, 'ip' => '127.0.0.1']);

        $this->assertEquals(1, $microblog->votes);

        $this
            ->json('GET', '/v1/microblogs/' . $microblog->id)
            ->assertStatus(200)
            ->assertJson(\array_merge(\array_except($microblog->toArray(), ['user_id', 'score', 'created_at', 'updated_at'])));
    }

    public function testShowOneWithNoVotes()
    {
        /** @var Microblog $microblog */
        $microblog = factory(Microblog::class)->create();

        $this
            ->json('GET', '/v1/microblogs/' . $microblog->id)
            ->assertJsonFragment(['votes' => 0]);
    }
}
