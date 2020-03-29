<?php

namespace Coyote\Http\Resources;

use Carbon\Carbon;
use Coyote\Forum;
use Coyote\Http\Resources\Api\PostResource;
use Coyote\Post;
use Coyote\Services\UrlBuilder\UrlBuilder;
use Coyote\Tag;
use Coyote\User;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property Carbon $locked_at
 * @property Carbon $created_at
 * @property Carbon $last_post_created_at
 * @property string $html
 * @property User $user
 * @property Forum $forum
 * @property Post $firstPost
 * @property Post $lastPost
 * @property Tag[] $tags
 * @property \Carbon\Carbon $read_at
 * @method bool isRead()
 * @property Post[] $posts
 * @property int $topic_last_post_id
 */
class TopicResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $only = $this->resource->only(['id', 'subject', 'score', 'views', 'replies', 'is_sticky', 'is_locked', 'first_post_id', 'last_post_id', 'subscribers', 'accepted_id', 'is_subscribed', 'is_voted', 'is_replied', 'user_name', 'user_post_id']);

        return array_merge(
            $only,
            [
                'created_at'            => $this->created_at->toIso8601String(),
                'last_post_created_at'  => $this->last_post_created_at->toIso8601String(),
                'url'                   => url(UrlBuilder::topic($this->resource->getModel())),
                'is_read'               => $this->isRead(),
                'forum'         => [
                    'id'        => $this->forum->id,
                    'name'      => $this->forum->name,
                    'slug'      => $this->forum->slug,
                    'url'       => UrlBuilder::forum($this->forum)
                ],
                'tags'                  => TagResource::collection($this->whenLoaded('tags')),

                $this->mergeWhen($this->whenLoaded('user'), function () {
                    return ['user' => new UserResource($this->user)];
                }),

                $this->mergeWhen($this->whenLoaded('lastPost'), function () {
                    $this->lastPost->setRelation('forum', $this->forum)->setRelation('topic', $this->resource);

                    return [
                        'last_post'            => new PostResource($this->lastPost),
                    ];
                })
            ]
        );
    }
}
