<?php
namespace Coyote\Http\Resources;

use Coyote\Post;
use Coyote\Services\UrlBuilder;
use Illuminate\Http\Request;

/**
 * Activity presented as a post-like card on the modern homepage,
 * which can be reacted to (voted on, commented on).
 */
class ActivityFeedResource extends ActivityResource
{
    public function toArray(Request $request): array
    {
        $activity = parent::toArray($request);
        $activity['user']['url'] = $this->user_id ? route('profile', ['user_trashed' => $this->user_id]) : null;
        $kind = $this->kind();
        return [
            ...$activity,
            'kind'     => $kind,
            'url'      => $this->contentUrl(),
            'topic'    => [
                'title' => $this->topic->title,
                'url'   => UrlBuilder::topic($this->topic->setRelation('forum', $this->forum)),
            ],
            'forum'    => [
                'name' => $this->forum->name,
                'url'  => UrlBuilder::forum($this->forum),
            ],
            'score'    => (int)$this->content->score,
            'comments' => $kind === 'post' ? (int)$this->content->comments_count : null,
            'replies'  => $kind === 'topic' ? (int)$this->topic->replies : null,
            'vote'     => [
                'type' => $this->content_type === Post::class ? 'post' : 'comment',
                'id'   => $this->content_id,
            ],
        ];
    }

    private function kind(): string
    {
        if ($this->content_type === Post::class) {
            return $this->topic->first_post_id === $this->content_id ? 'topic' : 'post';
        }
        return 'comment';
    }

    private function contentUrl(): string
    {
        if ($this->content_type === Post::class) {
            $this->content->setRelations(['topic' => $this->topic, 'forum' => $this->forum]);
            return UrlBuilder::post($this->content);
        }
        $post = (new Post)
            ->forceFill(['id' => $this->content->post_id])
            ->setRelations(['topic' => $this->topic, 'forum' => $this->forum]);
        $this->content->setRelations(['post' => $post]);
        return UrlBuilder::postComment($this->content);
    }
}
