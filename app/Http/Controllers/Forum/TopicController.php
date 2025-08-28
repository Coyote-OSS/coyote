<?php
namespace Coyote\Http\Controllers\Forum;

use Coyote\Domain\Seo;
use Coyote\Domain\Seo\Schema\DiscussionForumPosting;
use Coyote\Forum;
use Coyote\Forum\Reason;
use Coyote\Http\Resources\FlagResource;
use Coyote\Http\Resources\PollResource;
use Coyote\Http\Resources\PostCollection;
use Coyote\Http\Resources\TopicResource;
use Coyote\Post;
use Coyote\Repositories\Criteria\Post\WithSubscribers;
use Coyote\Repositories\Criteria\Post\WithTrashedInfo;
use Coyote\Repositories\Criteria\WithTrashed;
use Coyote\Services\Flags;
use Coyote\Services\Forum\Tracker;
use Coyote\Services\Forum\TreeBuilder\Builder;
use Coyote\Services\Forum\TreeBuilder\JsonDecorator;
use Coyote\Services\Forum\TreeBuilder\ListDecorator;
use Coyote\Services\Parser\Extensions\Emoji;
use Coyote\Services\UrlBuilder;
use Coyote\Topic;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class TopicController extends BaseController {
    public function index(Request $request, Forum $forum, Topic $topic): Collection|View|array {
        if (!$this->visibleDespiteIncognitoUser($topic)) {
            abort(404);
        }
        $this->breadcrumb->push($topic->title, route('forum.topic', [$forum->slug, $topic->id, $topic->slug]),
            leafWithLink:true);

        // get the topic (and forum) mark time value from middleware
        $markTime = $request->attributes->get('mark_time');

        /** @var Gate $gate */
        $gate = app(Gate::class);

        if ($topic->is_tree) {
            $page = 1;
            $perPage = 400;
        } else {
            $page = (int)$request->get('page');
            $perPage = $this->postsPerPage($request);
        }

        // user with forum-update ability WILL see every post
        // NOTE: criteria MUST BE pushed before calling getPage() method!
        $hasAccessToDeletedPosts = $gate->allows('delete', $forum);
        if ($hasAccessToDeletedPosts) {
            $this->post->pushCriteria(new WithTrashed());
            $this->post->pushCriteria(new WithTrashedInfo());

            $topic->replies = $topic->replies_real; // user is able to see real number of posts in this topic
        } else {
            if ($topic->is_tree) {
                $this->post->pushCriteria(new WithTrashed());
            }
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
        if ($page === 1) {
            $topic->setRelation('firstPost', $paginate->first());
        } else {
            $topic->load('firstPost');
        }

        $tracker = Tracker::make($topic);

        if ($hasAccessToDeletedPosts || $gate->allows('move', $forum)) {
            $reasons = Reason::query()->pluck('name', 'id')->toArray();
            $this->forum->resetCriteria();
            $this->pushForumCriteria(false);

            // forum list only for moderators
            $treeBuilder->setForums($this->forum->list());
            $allForums = (new JsonDecorator($treeBuilder))->build();
        } else {
            $allForums = [];
            $reasons = null;
        }

        $resource = new PostCollection($paginate);
        $resource->setRelations($topic, $forum);
        $resource->setTracker($tracker);
        $topicResource = new TopicResource($tracker);

        $isSubtree = false;
        $seeWholeUrl = null;
        $seePreviousUrl = null;
        if ($request->filled('p')) {
            $postId = $request->get('p');
            if (ctype_digit($postId)) {
                $post = Post::query()->withTrashed()->find($postId);
            } else {
                $post = null;
            }
            if ($post) {
                $resource->setSelectedPostId($postId);
                $topicResource->setSelectedPostId($postId);
                $isSubtree = true;
                $seeWholeUrl = UrlBuilder::topic($topic);
                if ($post->treeParentPost === null) {
                    $isSubtree = false;
                } else {
                    $seePreviousUrl = UrlBuilder::post($post->treeParentPost);
                }
            }
        }

        if (!$hasAccessToDeletedPosts) {
            $resource->obscureDeletedPosts();
        }

        $dateTime = $paginate->last()->created_at;

        TopicResource::wrap('data');
        $posts = $resource->toResponse($this->request)->getData(true);
        TopicResource::withoutWrapping();

        if ($markTime < $dateTime) {
            $tracker->asRead($dateTime);
        }

        if ($request->wantsJson()) {
            return $posts;
        }

        $topic->load('tags');

        $post = array_first($posts['data']);
        return $this
            ->view('forum.topic', [
                'threadStartUrl'          => route('forum.topic', [$forum->slug, $topic->id, $topic->slug]),
                'posts'                   => $posts,
                'forum'                   => $forum,
                'paginationCurrentPage'   => $paginate->currentPage(),
                'paginationPerPage'       => $paginate->perPage(),
                'paginationManyPages'     => $paginate->lastPage() > 1,
                'reasons'                 => $reasons,
                'model'                   => $topic, // we need eloquent model in twig to show information about locked/moved topic
                'topic'                   => $topicResource->toResponse($request)->getData(true),
                'treeTopicIsSubtree'      => $isSubtree,
                'treeTopicSeeWholeUrl'    => $seeWholeUrl,
                'treeTopicSeePreviousUrl' => $seePreviousUrl,
                'poll'                    => $topic->poll ? (new PollResource($topic->poll))->resolve($request) : null,
                'is_writeable'            => $gate->allows('write', $forum) && $gate->allows('write', $topic),
                'all_forums'              => $allForums,
                'emojis'                  => Emoji::all(),
                'user_forums'             => $userForums,
                'description'             => excerpt($post['text'], 100),
                'flags'                   => $this->flags($forum),
                'schema_topic'            => $this->discussionForumPosting($topic, $post['html']),
            ]);
    }

    private function discussionForumPosting(Topic $topic, string $html): Seo\Schema {
        $user = $topic->firstPost->user;
        return new Seo\Schema(new DiscussionForumPosting(
            route('forum.topic', [$topic->forum, $topic, $topic->slug]),
            $topic->title,
            \reduced_whitespace(\plain($html)),
            $user?->name ?? $topic->firstPost->user_name,
            $user ? route('profile', ['user_trashed' => $user->id]) : null,
            $topic->replies,
            $topic->score,
            $topic->views,
            $topic->created_at,
        ));
    }

    private function flags(Forum $forum): array {
        /** @var Flags $flags */
        $flags = resolve(Flags::class);
        $resourceFlags = $flags
            ->fromModels([Topic::class])
            ->permission('delete', [$forum])
            ->get();
        return FlagResource::collection($resourceFlags)->toArray($this->request);
    }

    public function mark(Topic $topic): void {
        $tracker = Tracker::make($topic);
        $tracker->asRead($topic->last_post_created_at);
    }

    private function visibleDespiteIncognitoUser(Topic $topic): bool {
        if ($topic->firstPost->user?->is_incognito) {
            return auth()->id() === $topic->firstPost->user_id;
        }
        return true;
    }
}
