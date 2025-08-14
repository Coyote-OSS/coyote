<?php
namespace Coyote\Http\Controllers\Forum;

use Coyote\Events\PostSaved;
use Coyote\Events\TopicSaved;
use Coyote\Forum;
use Coyote\Http\Controllers\Controller;
use Coyote\Http\Requests\Forum\PostRequest;
use Coyote\Http\Requests\Forum\SubjectRequest;
use Coyote\Http\Resources\PostResource;
use Coyote\Notifications\Topic\SubjectChangedNotification;
use Coyote\Post;
use Coyote\Repositories\Contracts\PollRepositoryInterface;
use Coyote\Services\Forum\Tracker;
use Coyote\Services\Parser\Extensions\Emoji;
use Coyote\Services\Parser\Factories\PostFactory;
use Coyote\Services\Stream\Activities\Create as Stream_Create;
use Coyote\Services\Stream\Activities\Update as Stream_Update;
use Coyote\Services\Stream\Actor as Stream_Actor;
use Coyote\Services\Stream\Objects\Forum as Stream_Forum;
use Coyote\Services\Stream\Objects\Post as Stream_Post;
use Coyote\Services\Stream\Objects\Topic as Stream_Topic;
use Coyote\Services\UrlBuilder;
use Coyote\Topic;
use Coyote\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class SubmitController extends BaseController {
    public function index(Forum $forum): View {
        $this->breadcrumb->push('Nowy wątek', route('forum.topic.submit', [$forum->slug]));
        return Controller::view('forum.submit', [
            'forum'                => $forum,
            'show_sticky_checkbox' => (int)$this->stickyNavbar($forum),
            'popular_tags'         => $this->forum->popularTags($forum->id),
            'emojis'               => Emoji::all(),

            'show_discuss_mode_select' => Gate::check('alpha-access'),
        ]);
    }

    private function stickyNavbar(Forum $forum): bool {
        return $this->userId && $this->auth->can('sticky', $forum);
    }

    public function save(PostRequest $request, Forum $forum, ?Topic $topic, Post $post): JsonResponse {
        /** @var User $user */
        $user = auth()->user();
        if (!$user->is_confirm) {
            abort(403, 'Potwierdź adres e-mail, by dodać post lub wątek.');
        }
        if (!$topic->exists) {
            $topic = $this->topic->makeModel();
            $topic->forum()->associate($forum);
            if ($request->get('discussMode') === 'tree') {
                Gate::authorize('alpha-access');
                $topic->is_tree = true;
            }
        }

        $topic->fill($request->only(array_keys($request->rules())));

        if (!$post->exists) {
            $post->forum()->associate($forum);
            if ($this->userId) {
                $post->user()->associate($this->auth);
            }
            $post->ip = $request->ip();
            $post->browser = str_limit($this->request->browser(), 250);
            $post->tree_parent_post_id = $this->request->get('treeAnswerPostId', null);
        } else {
            $this->authorize('update', [$post]);
            $post->topic()->associate($topic);
        }

        $previousPostMarkdown = $post->text;
        $post->fill($request->all());

        if ($post->isDirtyWithRelations() && $post->exists) {
            $post->fill([
                'edit_count' => $post->edit_count + 1,
                'editor_id'  => $this->auth->id,
            ]);
        }

        $post = $this->transaction(function () use ($forum, $topic, $post, $request) {
            $actor = new Stream_Actor($this->auth);

            if (auth()->guest()) {
                $actor->displayName = $request->get('user_name');
            }

            $activity = $post->id ? new Stream_Update($actor) : new Stream_Create($actor);

            $topic->poll()->associate($this->savePoll($request, $topic->poll_id));
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
                if ($this->auth->allow_subscribe) {
                    $topic->subscribe($this->userId, true);
                }
            }

            if ($topic->wasRecentlyCreated || $post->id === $topic->first_post_id) {
                $object = new Stream_Topic()->map($topic, $post->html);
                $target = new Stream_Forum()->map($forum);
                $topic->setTags(array_unique((array)$request->input('tags', [])));
            } else {
                $object = new Stream_Post(['url' => UrlBuilder::post($post)])->map($post);
                $target = new Stream_Topic()->map($topic);
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
        $previousPostHtml = null;
        if ($previousPostMarkdown !== null && $previousPostMarkdown !== $post->text) {
            /** @var PostFactory $factory */
            $factory = app('parser.post');
            $previousPostHtml = $factory->parse($previousPostMarkdown);
        }
        broadcast(new PostSaved($post, $previousPostHtml))->toOthers();

        $postResource = new PostResource($post);
        $postResource->setTracker($tracker);
        $response = $postResource->response($this->request);
        $post->wasRecentlyCreated ? $tracker->asRead($post->created_at) : null; // mark topic as read after publishing
        $response->setStatusCode($post->wasRecentlyCreated ? 201 : 200);
        return $response;
    }

    /**
     * @param Request $request
     * @param int $pollId
     * @return \Coyote\Poll|null
     */
    private function savePoll(Request $request, $pollId) {
        $items = array_filter($request->input('poll.items.*.text', []));

        if ($items || !$pollId) {
            if ($items) {
                return $this->getPollRepository()->updateOrCreate($pollId, $request->input('poll'));
            }
            if ($pollId) {
                return $this->getPollRepository()->find($pollId);
            }
        } else {
            $this->getPollRepository()->delete($pollId);
        }

        return null;
    }

    /**
     * @return PollRepositoryInterface
     */
    private function getPollRepository() {
        return app(PollRepositoryInterface::class);
    }

    /**
     * @param Topic $topic
     * @param SubjectRequest $request
     * @return string
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function subject($topic, SubjectRequest $request) {
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
                        ->setOriginalSubject(str_limit($originalSubject, 84)),
                );
            }

            // get text from cache to put excerpt in stream activity
            $post->text = app('parser.post')->parse($post->text);

            // put action into activity stream
            stream(
                Stream_Update::class,
                (new Stream_Topic)->map($topic, $post->text),
                (new Stream_Forum)->map($topic->forum),
            );
        });

        event(new TopicSaved($topic));
        event(new PostSaved($post, $post->html));

        return response()->json(['url' => UrlBuilder::topic($topic)]);
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function preview(Request $request) {
        $parser = app('parser.post');
        $parser->cache->setEnable(false);

        return response($parser->parse((string)$request->get('text')));
    }
}
