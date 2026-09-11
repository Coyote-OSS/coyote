<?php

namespace Tests\Legacy\LaravelIntegration\Models\Topic;

use Coyote\Tag;
use Coyote\Topic;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Legacy\LaravelIntegration\TestCase;

class TagTest extends TestCase
{
    use DatabaseTransactions;

    public function testToggleTagAndCheckCounter()
    {
        $tag = factory(Tag::class)->create(['resources' => ['Coyote\Topic' => 1]]);

        $this->assertEquals(1, $tag->resources['Coyote\Topic']);

        /** @var Topic $topic */
        $topic = factory(Topic::class)->create();
        $topic->tags()->sync([$tag->id]);

        $tag->refresh();

        $this->assertEquals(2, $tag->resources['Coyote\Topic']);

        $topic->tags()->sync([]);

        $tag->refresh();

        $this->assertEquals(1, $tag->resources['Coyote\Topic']);
    }
}
