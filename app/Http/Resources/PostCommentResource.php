<?php
namespace Coyote\Http\Resources;

use Carbon\Carbon;
use Coyote\Forum;
use Coyote\Post;
use Coyote\Services\UrlBuilder;
use Coyote\Topic;
use Coyote\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property User $user
 * @property Forum $forum
 * @property Post $post
 * @property int $user_id
 * @property int $post_id
 * @property string $text
 */
class PostCommentResource extends JsonResource {
    public function toArray(Request $request): array {
        /** @var Post\Comment $comment */
        $comment = $this->resource;
        return array_merge(
            $comment->only(['id', 'text', 'html', 'post_id']),
            [
                'created_at' => $this->created_at->toIso8601String(),
                'updated_at' => $this->updated_at->toIso8601String(),
                'user'       => new UserResource($this->user),
                'url'        => $this->commentUrl(),
                'metadata'   => \encrypt($this->metadata()),
                $this->mergeWhen($request->user(), fn() => [
                    'editable' => $request->user()->can('update', [$this->resource, $this->forum]),
                ]),
                'votes'      => $comment->score,
                'ownVote'    => false, // Introduce eager load, not fail at n+1
                'voters'     => [],  // Introduce eager load, not fail at n+1
            ],
        );
    }

    private function commentUrl(): string {
        return UrlBuilder::topic($this->post->topic) . '?p=' . $this->post_id . '#comment-' . $this->id;
    }

    private function metadata(): array {
        return [
            Post\Comment::class => $this->id,
            Post::class         => $this->post_id,
            Topic::class        => $this->post->topic->id,
            Forum::class        => $this->post->forum->id,
        ];
    }
}
