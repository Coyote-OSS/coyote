<?php
namespace Tests\Legacy\Integration\Seo\Schema\BreadcrumbList\Fixture;

use Tests\Legacy\Integration\Seo;

trait Schema
{
    use Seo\Schema\Fixture\Schema, Seo\Schema\BreadcrumbList\Fixture\Models;

    function breadcrumbsSchema(): array
    {
        return $this->schema('/Forum', type:'BreadcrumbList');
    }

    function categorySchema(string $forumName, string $forumSlug): array
    {
        $this->newForum($forumName, $forumSlug);
        return $this->schema("/Forum/$forumSlug", 'BreadcrumbList');
    }

    function categoryWithParentSchema(string $parentName, string $parentSlug): array
    {
        $childSlug = $this->newChildForum($parentName, $parentSlug);
        return $this->schema("/Forum/$childSlug", 'BreadcrumbList');
    }

    function topicSchema(string $topicTitle, string $forumSlug): array
    {
        $topic = $this->newThread($topicTitle, $forumSlug);
        return $this->schema("/Forum/{$topic->forum->slug}/{$topic->id}-{$topic->slug}", 'BreadcrumbList');
    }

    function categorySchemaAny(): array
    {
        return $this->categorySchema('irrelevant', 'irrelevant');
    }

    function topicSchemaAny(): array
    {
        return $this->topicSchema('irrelevant', 'irrelevant');
    }
}
