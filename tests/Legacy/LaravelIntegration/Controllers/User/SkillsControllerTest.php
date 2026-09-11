<?php

namespace Tests\Legacy\LaravelIntegration\Controllers\User;

use Coyote\Tag;
use Coyote\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Legacy\LaravelIntegration\TestCase;

class SkillsControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function testUpdateSkillsRate()
    {
        /** @var User $userA */
        $userA = factory(User::class)->create();
        /** @var User $userB */
        $userB = factory(User::class)->create();

        $tag = factory(Tag::class)->create();

        $userA->skills()->attach($tag->id, ['priority' => 1]);
        $userB->skills()->attach($tag->id, ['priority' => 1]);

        $response = $this->actingAs($userB)->postJson("/User/Skills/$tag->id", ['priority' => 2]);
        $response->assertOk();

        $userB->refresh();
        $userA->refresh();

        $this->assertEquals(2, $userB->skills->first()->pivot->priority);
        $this->assertEquals(1, $userA->skills->first()->pivot->priority);
    }
}
