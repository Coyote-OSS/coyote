<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Forum;
use Coyote\Http\Controllers\Controller;
use Coyote\Http\Requests\Forum\PostRequest;
use Coyote\Http\Requests\Forum\SubjectRequest;
use Coyote\Http\Resources\PostResource;
use Coyote\Notifications\Topic\SubjectChangedNotification;
use Coyote\Post;
use Coyote\Repositories\Contracts\PollRepositoryInterface;
use Coyote\Services\Forum\Tracker;
use Coyote\Services\UrlBuilder;
use Coyote\Topic;
use Illuminate\Http\Request;
use Coyote\Services\Stream\Activities\Create as Stream_Create;
use Coyote\Services\Stream\Activities\Update as Stream_Update;
use Coyote\Services\Stream\Objects\Topic as Stream_Topic;
use Coyote\Services\Stream\Objects\Post as Stream_Post;
use Coyote\Services\Stream\Objects\Forum as Stream_Forum;
use Coyote\Services\Stream\Actor as Stream_Actor;
use Coyote\Events\PostSaved;
use Coyote\Events\TopicSaved;

class SubmitController extends BaseController
{
    /**
     * Show new post/edit form
     *
     * @param \Coyote\Forum $forum
     * @return \Illuminate\View\View
     */
    public function index($forum)
    {
        $this->breadcrumb->push('Nowy wątek', route('forum.topic.submit', [$forum->slug]));

        return Controller::view('forum.submit', [
            'forum' => $forum,
            'show_sticky_checkbox' => (int) ($this->userId ? $this->auth->can('sticky', $forum) : false),
            'popular_tags' => $this->forum->popularTags($forum->id)
        ]);
    }

    /**
     * @param PostRequest $request
     * @param Forum $forum
     * @param Topic|null $topic
     * @param Post|null $post
     * @return array
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function save(PostRequest $request, Forum $forum, ?Topic $topic, ?Post $post)
    {
        if (!$topic->exists) {
            $topic = $this->topic->makeModel();
            $topic->forum()->associate($forum);
        }

        $topic->fill($request->only(array_keys($request->rules())));

        if (!$post->exists) {
            $post->forum()->associate($forum);

            if ($this->userId) {
                $post->user()->associate($this->auth);
            }

            $post->ip = $request->ip();
            $post->browser = str_limit($this->request->browser(), 250);
        } else {
            $this->authorize('update', [$post]);

            $post->topic()->associate($topic);
        }

        $post->fill($request->all());

        if ($post->isDirtyWithRelations() && $post->exists) {
            $post->fill([
                'edit_count' => $post->edit_count + 1, 'editor_id' => $this->auth->id
            ]);
        }

        $post = $this->transaction(function () use ($forum, $topic, $post, $request) {
            $actor = new Stream_Actor($this->auth);

            if (auth()->guest()) {
                $actor->displayName = $request->get('user_name');
            }

            $activity = $post->id ? new Stream_Update($actor) : new Stream_Create($actor);

            $poll = $this->savePoll($request, $topic->poll_id);
            $topic->poll()->associate($poll);

            $topic->save();

            $post->topic()->associate($topic);
            $post->save();

            $post->assets()->sync($request->input('assets'));

            if ($topic->wasRecentlyCreated) {
                $topic->first_post_id = $post->id;
            }

            if ($post->wasRecentlyCreated && $this->userId) {
                $topic->last_post_id = $post->id;

                $post->subscribe($this->userId, true);
                $topic->subscribe($this->userId, $this->auth->allow_subscribe);
            }

            // url to the post
            $url = UrlBuilder::post($post);

            if ($topic->wasRecentlyCreated || $post->id === $topic->first_post_id) {
                $object = (new Stream_Topic)->map($topic, $post->html);
                $target = (new Stream_Forum)->map($forum);

                $tags = array_unique((array) $request->input('tags', []));
                $topic->setTags($tags);
            } else {
                $object = (new Stream_Post(['url' => $url]))->map($post);
                $target = (new Stream_Topic())->map($topic);
            }

            stream($activity, $object, $target);

            return $post;
        });

        $tracker = Tracker::make($topic);

        PostResource::withoutWrapping();

        $post->unsetRelation('assets');
        $post->load('assets');

        // fire the event. it can be used to index a content and/or add page path to "pages" table
        event(new TopicSaved($topic));
        // add post to elasticsearch
        broadcast(new PostSaved($post))->toOthers();

        $resource = (new PostResource($post))->setTracker($tracker)->resolve($this->request);

        // mark topic as read after publishing
        $post->wasRecentlyCreated ? $tracker->asRead($post->created_at) : null;

        return $resource;
    }

    /**
     * @param Request $request
     * @param int $pollId
     * @return \Coyote\Poll|null
     */
    private function savePoll(Request $request, $pollId)
    {
        $items = array_filter($request->input('poll.items.*.text', []));

        if (!$items && $pollId) {
            $this->getPollRepository()->delete($pollId);
        } elseif ($items) {
            return $this->getPollRepository()->updateOrCreate($pollId, $request->input('poll'));
        } elseif ($pollId) {
            return $this->getPollRepository()->find($pollId);
        }

        return null;
    }

    /**
     * @return PollRepositoryInterface
     */
    private function getPollRepository()
    {
        return app(PollRepositoryInterface::class);
    }

    /**
     * @param Topic $topic
     * @param SubjectRequest $request
     * @return string
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function subject($topic, SubjectRequest $request)
    {
        $this->authorize('update', $topic->forum);

        $topic->fill(['title' => $request->input('title')]);

        if (!$topic->isDirty()) {
            return response()->json(['url' => UrlBuilder::topic($topic)]);
        }

        /** @var \Coyote\Post $post */
        $post = $topic->firstPost;

        $this->transaction(function () use ($request, $topic, $post) {
            $originalSubject = $topic->getRawOriginal('title');

            $topic->save();
            $post->fill(['edit_count' => $post->edit_count + 1, 'editor_id' => $this->userId])->save();

            if ($post->user_id !== null && $post->user_id !== $this->userId) {
                $post->user->notify(
                    (new SubjectChangedNotification($this->auth, $topic))
                        ->setOriginalSubject(str_limit($originalSubject, 84))
                );
            }

            // get text from cache to put excerpt in stream activity
            $post->text = app('parser.post')->parse($post->text);

            // put action into activity stream
            stream(
                Stream_Update::class,
                (new Stream_Topic)->map($topic, $post->text),
                (new Stream_Forum)->map($topic->forum)
            );
        });

        // fire the event. it can be used to index a content and/or add page path to "pages" table
        event(new TopicSaved($topic));
        // add post to elasticsearch
        event(new PostSaved($post));

        return response()->json(['url' => UrlBuilder::topic($topic)]);
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function preview(Request $request)
    {
        $parser = app('parser.post');
        $parser->cache->setEnable(false);

        return response($parser->parse((string) $request->get('text')));
    }
}
