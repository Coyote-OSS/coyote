<?php
namespace Coyote\Http\Controllers\Forum;

use Coyote\Events\CommentDeleted;
use Coyote\Events\CommentSaved;
use Coyote\Events\PostSaved;
use Coyote\Events\TopicSaved;
use Coyote\Http\Controllers\Controller;
use Coyote\Http\Requests\Forum\PostCommentRequest;
use Coyote\Http\Resources\PostCommentResource;
use Coyote\Http\Resources\PostResource;
use Coyote\Notifications\Post\Comment;
use Coyote\Notifications\Post\Comment\MigratedNotification;
use Coyote\Post;
use Coyote\Repositories\Contracts\TopicRepositoryInterface;
use Coyote\Services\Forum\Tracker;
use Coyote\Services\Stream\Activities\Create as Stream_Create;
use Coyote\Services\Stream\Activities\Delete as Stream_Delete;
use Coyote\Services\Stream\Activities\Move as Stream_Move;
use Coyote\Services\Stream\Activities\Update as Stream_Update;
use Coyote\Services\Stream\Objects\Comment as Stream_Comment;
use Coyote\Services\Stream\Objects\Topic as Stream_Topic;
use Coyote\Stream;
use Coyote\User;

class CommentController extends Controller {
    public function save(PostCommentRequest $request, Post\Comment $comment): PostCommentResource {
        /** @var User $user */
        $user = auth()->user();
        if (!$user->is_confirm) {
            abort(403, 'Potwierdź adres e-mail, by dodać komentarz.');
        }
        if (!$comment->exists) {
            $comment->user()->associate($this->auth);
            $comment->post_id = $request->input('post_id');
            $comment->score = 0;
        } else {
            $this->authorize('update', [$comment, $comment->post->forum]);
        }

        // Maybe user does not have an access to this category?
        $this->authorize('access', [$comment->post->forum]);
        // Only moderators can post comment if topic (or forum) was locked
        $this->authorize('write', [$comment]);

        $comment->fill($request->only(['text']));

        $this->transaction(function () use ($comment) {
            $comment->save();

            stream($comment->wasRecentlyCreated ? Stream_Create::class : Stream_Update::class, ...$this->target($comment));

            if ($comment->wasRecentlyCreated) {
                // subscribe post. notify about all future comments to this post
                $comment->post->subscribe($this->userId, true);
            }
        });

        $comment->setRelation('forum', $comment->post->forum);

        broadcast(new CommentSaved($comment))->toOthers();

        PostCommentResource::withoutWrapping();
        return new PostCommentResource($comment)->additional([
            'is_subscribed' => $comment->post
                ->subscribers()
                ->forUser($this->userId)
                ->exists(),
        ]);
    }

    /**
     * @param Post\Comment $comment
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function delete(Post\Comment $comment) {
        abort_if(!$comment->post, 404);

        $this->authorize('delete', [$comment, $comment->post->forum]);

        $this->transaction(function () use ($comment) {
            $comment->delete();

            stream(Stream_Delete::class, ...$this->target($comment));
        });

        event(new CommentDeleted($comment));
    }

    /**
     * @param Post $post
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function getAll(Post $post) {
        $this->authorize('access', [$post->forum]);

        PostCommentResource::withoutWrapping();

        $post->load('comments.user');

        $post->comments->each(function (Post\Comment $comment) use ($post) {
            $comment->setRelation('forum', $post->forum);
        });

        return PostCommentResource::collection($post->comments)->keyBy('id');
    }

    public function show(Post\Comment $comment): PostCommentResource {
        // post can be already removed.
        abort_if($comment->post === null, 404);

        $this->authorize('access', [$comment->post->forum]);
        $comment->setRelation('forum', $comment->post->forum);

        PostCommentResource::withoutWrapping();

        return new PostCommentResource($comment);
    }

    public function migrate(Post\Comment $comment, TopicRepositoryInterface $repository) {
        $topic = $comment->post->topic;

        // Maybe user does not have an access to this category?
        $this->authorize('access', [$comment->post->forum]);
        // Only moderators can post comment if topic (or forum) was locked
        $this->authorize('write', [$comment]);
        $this->authorize('merge', $topic->forum);

        /** @var Post $post */
        $post = $this->transaction(function () use ($topic, $comment, $repository) {
            $stream = Stream::where('object->objectType', 'comment')->where('object->id', $comment->id)->first();

            /** @var Post $post */
            $post = $topic->posts()->forceCreate(
                array_merge(
                    ['forum_id' => $topic->forum_id, 'topic_id' => $topic->id, 'ip' => $stream->ip, 'browser' => $stream->browser],
                    $comment->only(['created_at', 'text', 'user_id']),
                ),
            );

            if ($this->userId !== $comment->user_id) {
                $post->user->notify(new MigratedNotification($this->auth, $post));
            }

            if ($post->user->allow_subscribe) {
                $topic->subscribe($post->user_id, true);
            }

            $comment->delete();
            $repository->adjustReadDate($topic->id, $comment->created_at->subSecond());

            stream(Stream_Move::class, ...$this->target($comment));

            return $post;
        });

        $post->load('assets');
        $tracker = Tracker::make($topic);

        // fire the event. it can be used to index a content and/or add page path to "pages" table
        event(new TopicSaved($topic));
        // add post to elasticsearch
        broadcast(new PostSaved($post))->toOthers();

        event(new CommentDeleted($comment));

        PostResource::withoutWrapping();

        $postResource = new PostResource($post);
        $postResource->setTracker($tracker);
        return $postResource->resolve($this->request);
    }

    public function vote(Post\Comment $comment): array {
        $currentUserId = auth()->user()->id;
        if ($comment->user_id !== $currentUserId) {
            $comment->voters()->toggle($currentUserId);
        } else {
            abort(400, 'Nie możesz oddać głosu na komentarz swojego autorstwa.');
        }
        $score = $comment->voters()->count();
        $comment->update(['score' => $score]);
        $voters = $comment->voters->pluck('name', 'id');
        $comment->user->notify(new Comment\VotedNotification($this->auth, $comment->post));
        return [
            'votes'    => $score,
            'voters'   => $voters->values()->toArray(),
            'own_vote' => $voters->has(auth()->user()->id),
        ];
    }

    public function getVotes(Post\Comment $comment): array {
        return [
            'votes'  => $comment->voters->count(),
            'voters' => $comment->voters->pluck('name')->toArray(),
        ];
    }

    private function target(Post\Comment $comment): array {
        $target = (new Stream_Topic())->map($comment->post->topic);

        // it is IMPORTANT to parse text first, and then put information to activity stream.
        // so that we will save plan text (without markdown)
        $object = (new Stream_Comment())->map($comment->post, $comment, $comment->post->topic);

        return [$object, $target];
    }
}
