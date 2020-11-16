<?php

namespace Tests\Unit\Models\Job;

use Coyote\Job;
use Coyote\Tag;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TagTest extends TestCase
{
    use DatabaseTransactions;

    public function testToggleTagAndCheckCounter()
    {
        $tag = factory(Tag::class)->create();

        /** @var Job $topic */
        $job = factory(Job::class)->create();
        $job->tags()->sync([$tag->id]);

        $tag->refresh();

        $this->assertEquals(1, $tag->jobs);

        $job->tags()->sync([]);

        $tag->refresh();

        $this->assertEquals(0, $tag->jobs);
    }
}
