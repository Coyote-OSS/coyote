<?php

namespace Tests\Legacy\LaravelIntegration\Models;

use Coyote\Forum;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Legacy\LaravelIntegration\TestCase;

class ForumTest extends TestCase
{
    use DatabaseTransactions;

    public function testForumCreationOrder()
    {
        $forumA = factory(Forum::class)->create();
        $forumB = factory(Forum::class)->create();

        $this->assertDatabaseHas('forums', ['name' => $forumA->name]);

        $forumA = Forum::firstOrNew(['name' => $forumA->name]);
        $forumB = Forum::firstOrNew(['name' => $forumB->name]);

        $this->assertEquals($forumA->order + 1, $forumB->order);
    }

    public function testForumDelete()
    {
        $forumA = factory(Forum::class)->create();
        $forumB = factory(Forum::class)->create();

        $before = Forum::firstOrNew(['name' => $forumB->name]);

        Forum::where('name', $forumA->name)->delete();

        $after = Forum::firstOrNew(['name' => $forumB->name]);

        $this->assertEquals($before->order - 1, $after->order);
    }
}
