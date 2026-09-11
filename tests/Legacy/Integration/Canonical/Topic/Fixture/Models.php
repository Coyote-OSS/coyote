<?php
namespace Tests\Legacy\Integration\Canonical\Topic\Fixture;

use Coyote\Forum;
use Coyote\Topic;
use Tests\Legacy\Integration\BaseFixture;

trait Models
{
    use BaseFixture\Forum\Store;

    function newTopic(): string
    {
        $topic = $this->storeThread(new Forum, new Topic);
        return "{$topic->forum->slug}/{$topic->id}-{$topic->slug}";
    }

    function newForumTopic(string $forumSlug, string $topicTitle): int
    {
        $topic = $this->storeThread(
            new Forum(['slug' => $forumSlug]),
            new Topic(['title' => $topicTitle]));
        return $topic->id;
    }

    function newForumSlug(string $slug): void
    {
        $forum = new Forum([
            'slug'        => $slug,
            'name'        => 'irrelevant',
            'description' => 'irrelevant',
        ]);
        $forum->save();
    }
}
