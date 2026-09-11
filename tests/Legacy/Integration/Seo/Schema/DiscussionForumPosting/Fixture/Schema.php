<?php
namespace Tests\Legacy\Integration\Seo\Schema\DiscussionForumPosting\Fixture;

use Coyote\Topic;
use Tests\Legacy\Integration\Seo;

trait Schema
{
    use Seo\Schema\Fixture\Schema, Seo\Schema\DiscussionForumPosting\Fixture\Models;

    function schemaTopicInForum(string $topicTitle, string $forumSlug): array
    {
        $topic = $this->newThread($topicTitle, $forumSlug);
        return [$this->postingSchema($topic), $topic->id];
    }

    function schemaTopicTitle(string $title): array
    {
        return $this->postingSchema($this->newTopicTitle($title));
    }

    function schemaForumStatistic(int $replies, int $likes, int $views): array
    {
        return $this->postingSchema($this->newTopicStatistic($replies, $likes, $views));
    }

    function schemaTopicContent(string $content): array
    {
        return $this->postingSchema($this->newThreadPostContent($content));
    }

    function schemaTopicCreatedAt(string $date, string $timezone): array
    {
        return $this->postingSchema($this->newTopicCreatedAt($date, $timezone));
    }

    function postingSchema(Topic $topic): array
    {
        return $this->schema("/Forum/{$topic->forum->slug}/$topic->id-{$topic->slug}", 'DiscussionForumPosting');
    }
}
