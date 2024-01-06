<?php
namespace Coyote\Http\Controllers\Forum;

use Coyote\Domain\Seo;
use Coyote\Domain\Seo\Schema\DiscussionForumPosting;
use Coyote\Forum;
use Coyote\Forum\Reason;
use Coyote\Http\Factories\CacheFactory;
use Coyote\Http\Resources\FlagResource;
use Coyote\Http\Resources\PollResource;
use Coyote\Http\Resources\PostCollection;
use Coyote\Http\Resources\TopicResource;
use Coyote\Repositories\Criteria\Forum\OnlyThoseWithAccess;
use Coyote\Repositories\Criteria\Post\WithSubscribers;
use Coyote\Repositories\Criteria\Post\WithTrashedInfo;
use Coyote\Repositories\Criteria\WithTrashed;
use Coyote\Services\Elasticsearch\Builders\Forum\MoreLikeThisBuilder;
use Coyote\Services\Flags;
use Coyote\Services\Forum\Tracker;
use Coyote\Services\Forum\TreeBuilder\Builder;
use Coyote\Services\Forum\TreeBuilder\JsonDecorator;
use Coyote\Services\Forum\TreeBuilder\ListDecorator;
use Coyote\Services\Parser\Extensions\Emoji;
use Coyote\Topic;
use Coyote\View\Twig\TwigLiteral;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use function app;

class TopicController extends BaseController
{
    use CacheFactory;

    public function index(Request $request, Forum $forum, Topic $topic): Collection|View|array
    {
        $this->breadcrumb->push($topic->title, route('forum.topic', [$forum->slug, $topic->id, $topic->slug]));

        // get the topic (and forum) mark time value from middleware
        $markTime = $request->attributes->get('mark_time');

        /** @var Gate $gate */
        $gate = app(Gate::class);

        $page = (int)$request->get('page');
        $perPage = $this->postsPerPage($request);

        // user with forum-update ability WILL see every post
        // NOTE: criteria MUST BE pushed before calling getPage() method!
        if ($gate->allows('delete', $forum)) {
            $this->post->pushCriteria(new WithTrashed());
            $this->post->pushCriteria(new WithTrashedInfo());

            $topic->replies = $topic->replies_real; // user is able to see real number of posts in this topic
        }

        // user wants to show certain post. we need to calculate page number based on post id.
        if ($request->filled('p')) {
            $page = $this->post->getPage(min(2147483647, (int)$request->get('p')), $topic->id, $perPage);
        }

        // show posts of last page if page parameter is higher than pages count
        $lastPage = max((int)ceil(($topic->replies + 1) / $perPage), 1);
        if ($page > $lastPage) {
            $page = $lastPage;
        }

        $this->post->pushCriteria(new WithSubscribers($this->userId));
        $paginate = $this->post->lengthAwarePagination($topic, $page, $perPage);
        $this->pushForumCriteria(true);

        // create forum list for current user (according to user's privileges)
        $treeBuilder = new Builder($this->forum->list());
        $treeDecorator = new ListDecorator($treeBuilder);

        $userForums = $treeDecorator->build();

        // important: load topic owner so we can highlight user's login
        $page === 1 ? $topic->setRelation('firstPost', $paginate->first()) : $topic->load('firstPost');

        $tracker = Tracker::make($topic);

        if ($gate->allows('delete', $forum) || $gate->allows('move', $forum)) {
            $reasons = Reason::pluck('name', 'id')->toArray();

            $this->forum->resetCriteria();
            $this->pushForumCriteria(false);

            // forum list only for moderators
            $treeBuilder->setForums($this->forum->list());
            $allForums = (new JsonDecorator($treeBuilder))->build();
        } else {
            $allForums = [];
            $reasons = null;
        }

        $resource = (new PostCollection($paginate))
            ->setRelations($topic, $forum)
            ->setTracker($tracker);

        $dateTime = $paginate->last()->created_at;
        // first, build array of posts with info which posts have been read
        // assign array ot posts variable. this is our skeleton! do not remove
        $posts = $resource->toResponse($this->request)->getData(true);

        if ($markTime < $dateTime) {
            $tracker->asRead($dateTime);
        }

        if ($request->wantsJson()) {
            return $posts;
        }

        $topic->load('tags');

        TopicResource::withoutWrapping();

        $firstPost = array_first($posts['data']);

        return $this
            ->view('forum.topic', compact('posts', 'forum', 'paginate', 'reasons'))
            ->with([
                'mlt'          => $this->moreLikeThis($topic),
                'model'        => $topic, // we need eloquent model in twig to show information about locked/moved topic
                'topic'        => (new TopicResource($tracker))->toResponse($request)->getData(true),
                'poll'         => $topic->poll ? (new PollResource($topic->poll))->resolve($request) : null,
                'is_writeable' => $gate->allows('write', $forum) && $gate->allows('write', $topic),
                'all_forums'   => $allForums,
                'emojis'       => Emoji::all(),
                'user_forums'  => $userForums,
                'description'  => excerpt($firstPost['text'], 100),
                'flags'        => $this->flags($forum),
                'schema_topic' => TwigLiteral::fromHtml($this->topicSchema(
                    $forum,
                    $topic,
                    reduce_whitespace(plain($firstPost['html'])))),
            ]);
    }

    private function topicSchema(Forum $forum, Topic $topic, string $content): Seo\Schema
    {
        [$username, $uri] = $this->author($topic);
        return new Seo\Schema(new DiscussionForumPosting(
            $topic->title,
            $content,
            route('forum.topic', [$forum, $topic, $topic->slug]),
            $topic->created_at,
            $username,
            $uri,
            $topic->views,
            $topic->score,
            $topic->replies,
        ));
    }

    private function author(Topic $topic): array
    {
        $user = $topic->firstPost->user;
        if ($user) {
            return [$user->name, route('profile', ['user_trashed' => $user])];
        }
        return [$topic->firstPost->user_name, null];
    }

    private function moreLikeThis(Topic $topic)
    {
        // build "more like this" block. it's important to send elasticsearch query before
        // send SQL query to database because search() method exists only in Model and not Builder class.
        return $this
            ->getCacheFactory()
            ->remember("mlt-post:$topic->id", now()->addDay(), function () use ($topic) {
                $this->forum->resetCriteria();
                $this->forum->pushCriteria(new OnlyThoseWithAccess());
                return $this->topic->search(new MoreLikeThisBuilder($topic, $this->forum->pluck('id')));
            });
    }

    private function flags(Forum $forum): array
    {
        $flags = resolve(Flags::class)->fromModels([Topic::class])->permission('delete', [$forum])->get();
        return FlagResource::collection($flags)->toArray($this->request);
    }

    public function mark(Topic $topic): void
    {
        $tracker = Tracker::make($topic);
        $tracker->asRead($topic->last_post_created_at);
    }
}
