<?php
namespace Tests\Legacy\Integration\BaseFixture\Forum;

use Coyote\Forum;
use Coyote\Post;
use Coyote\Topic;
use Tests\Legacy\Integration\BaseFixture\Server\Laravel;

trait Store
{
    use Laravel\Transactional;

    function storeThread(Forum $forum, Topic $topic, ?Post $post = null): Topic
    {
        $this->storeForum($forum);

        $topic->forum_id = $forum->id;
        $topic->title ??= 'irrelevant';
        $topic->save();

        if ($post) {
            $post->text ??= 'irrelevant';
            $post->user_name ??= 'irrelevant';
            $post->ip = 'irrelevant';
            $post->forum_id = $forum->id;
            $post->topic_id = $topic->id;
            $post->save();
            $topicPost = $post;
        } else {
            $topicPost = $this->placeholderPost($topic);
        }
        $topic->first_post_id = $topicPost->id;

        $topic->save();
        return $topic;
    }

    function storeForum(Forum $forum): void
    {
        $forum->description = 'irrelevant';
        $forum->slug ??= 'irrelevant_' . \preg_replace('/\d+/', 'x', uniqid());
        /** We can't set duplicated slug, because RedirectIfMoved
         * doesn't properly handle duplicated slugs and infinite
         * redirects occur. Additionally, {forum} pattern in routes
         * doesn't accept digits. */
        $forum->title ??= 'irrelevant';
        $forum->name ??= 'irrelevant';
        $forum->save();
    }

    function placeholderPost(Topic $topic): Post
    {
        $post = new Post;
        $post->text = 'irrelevant';
        $post->ip = 'irrelevant';
        $post->user_name = 'irrelevant';
        $post->topic_id = $topic->id;
        $post->forum_id = $topic->forum_id;
        $post->save();
        return $post;
    }
}
