<?php
namespace Tests\Legacy\Integration\Seo\Meta\Fixture;

use Coyote\Forum;
use Coyote\Topic;
use Tests\Legacy\Integration\BaseFixture;

trait Models
{
    use BaseFixture\Forum\Store;

    function newTopic(): array
    {
        $topic = $this->storeThread(new Forum, new Topic);
        return [$topic->forum->slug, "{$topic->id}-{$topic->slug}"];
    }

    function newCategory(string $slug): void
    {
        $this->storeForum(new Forum(['slug' => $slug]));
    }
}
