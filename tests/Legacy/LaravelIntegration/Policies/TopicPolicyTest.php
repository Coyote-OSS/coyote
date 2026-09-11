<?php

namespace Tests\Legacy\LaravelIntegration\Policies;

use Coyote\Topic;
use Illuminate\Support\Facades\Gate;

class TopicPolicyTest extends ForumPolicyTest
{
    /**
     * @var Topic
     */
    private $topic;

    public function setUp(): void
    {
        parent::setUp();

        $this->topic = factory(Topic::class)->make();
    }

    public function testWriteInLockedTopicIsNotAllowed()
    {
        $this->topic->is_locked = true;

        $this->assertFalse(Gate::allows('write', $this->topic));
        $this->assertFalse($this->user->can('write', $this->topic));
    }

    public function testWriteInLockedTopicAsAdminIsAllowed()
    {
        $this->topic->is_locked = true;

        Gate::define('forum-update', function () {
            return true;
        });

        $this->assertTrue($this->user->can('write', $this->topic));
    }
}
