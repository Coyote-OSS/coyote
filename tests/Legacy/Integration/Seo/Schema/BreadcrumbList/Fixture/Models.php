<?php
namespace Tests\Legacy\Integration\Seo\Schema\BreadcrumbList\Fixture;

use Coyote\Forum;
use Coyote\Topic;
use Tests\Legacy\Integration\BaseFixture;

trait Models
{
    use BaseFixture\Forum\Store;

    function newThread(string $topicTitle, string $forumSlug): Topic
    {
        return $this->storeThread(
            new Forum(['slug' => $forumSlug]),
            new Topic(['title' => $topicTitle]));
    }

    function newForum(string $name, string $slug): void
    {
        $forum = new Forum([
            'name'        => $name,
            'slug'        => $slug,
            'description' => 'irrelevant',
        ]);
        $forum->save();
    }

    function newChildForum(string $parentName, string $parentSlug): string
    {
        $parent = new Forum([
            'name'        => $parentName,
            'slug'        => $parentSlug,
            'description' => 'irrelevant',
        ]);
        $parent->save();

        $child = new Forum([
            'name'        => 'child',
            'slug'        => "$parent->slug/child",
            'description' => 'irrelevant',
            'parent_id'   => $parent->id,
        ]);
        $child->save();

        return $child->slug;
    }
}
