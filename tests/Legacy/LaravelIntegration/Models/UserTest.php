<?php

namespace Tests\Legacy\LaravelIntegration\Models;

use Coyote\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Legacy\LaravelIntegration\TestCase;

class UserTest extends TestCase
{
    use DatabaseTransactions;

    public function testShowFollowers()
    {
        /** @var User $user */
        $user = factory(User::class)->create();
        $follower = factory(User::class)->create();

        $follower->relations()->create(['related_user_id' => factory(User::class)->create()->id, 'is_blocked' => true]);
        $follower->relations()->create(['related_user_id' => $user->id, 'is_blocked' => false]);

        $this->assertEquals(1, count($user->followers));
        $this->assertEquals($follower->name, $user->followers[0]->name);
    }
}
