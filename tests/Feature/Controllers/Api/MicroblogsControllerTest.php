<?php

namespace Tests\Feature\Controllers\Api;

use Coyote\Microblog;
use Coyote\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class MicroblogsControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function testPagination()
    {
        $microblog = factory(Microblog::class)->create();
        $response = $this->json('GET', '/v1/microblogs');

        $response
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

        $response = $this->json('GET', '/v1/microblogs/' . $microblog->id);

        $response
            ->assertStatus(200)
            ->assertJson(array_merge(
                    array_except($microblog->toArray(), ['user_id', 'score']),
                    [
                        'comments' => [],
                        'media' => [],
                        'created_at' => $microblog->created_at->toIso8601String(),
                        'updated_at' => $microblog->created_at->toIso8601String(),
                        'html' => $microblog->html,
                        'user' => [
                            'id' => $microblog->user->id,
                            'name' => $microblog->user->name,
                            'deleted_at' => null,
                            'is_blocked' => false,
                            'photo' => null
                        ],
                        'votes' => 1,
                        'voters' => [$user->name]
                    ]
                )
            );
    }

    public function testShowOneWithNoVotes()
    {
        /** @var Microblog $microblog */
        $microblog = factory(Microblog::class)->create();

        $response = $this->json('GET', '/v1/microblogs/' . $microblog->id);

        $response
            ->assertJsonFragment([
                'votes' => 0,
                'voters' => []
            ]);
    }
}
