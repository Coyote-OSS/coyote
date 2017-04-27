<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Forum;
use Coyote\Forum\Reason;
use Coyote\Http\Factories\CacheFactory;
use Coyote\Http\Factories\FlagFactory;
use Coyote\Repositories\Contracts\StreamRepositoryInterface as StreamRepository;
use Coyote\Repositories\Contracts\UserRepositoryInterface as User;
use Coyote\Repositories\Criteria\Forum\OnlyThoseWithAccess;
use Coyote\Repositories\Criteria\Post\ObtainSubscribers;
use Coyote\Repositories\Criteria\Post\WithTrashed;
use Coyote\Services\Elasticsearch\Builders\Forum\MoreLikeThisBuilder;
use Coyote\Services\Forum\TreeBuilder;
use Coyote\Services\Parser\Parsers\ParserInterface;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Coyote\Topic;

class TopicController extends BaseController
{
    use CacheFactory, FlagFactory;

    /**
     * @var \Illuminate\Contracts\Auth\Access\Gate
     */
    private $gate;

    /**
     * @param Request $request
     * @param \Coyote\Forum $forum
     * @param \Coyote\Topic $topic
     * @return \Illuminate\View\View
     */
    public function index(Request $request, $forum, $topic)
    {
        // get the topic (and forum) mark time value from middleware
        // @see \Coyote\Http\Middleware\ScrollToPost
        $markTime = $request->attributes->get('mark_time');

        $this->gate = $this->getGateFactory();

        // current page...
        $page = (int) $request->get('page');
        // number of answers
        $replies = $topic->replies;
        // number of posts per one page
        $perPage = $this->postsPerPage($request);

        // user with forum-update ability WILL see every post
        if ($this->gate->allows('delete', $forum)) {
            $this->post->pushCriteria(new WithTrashed());
            // user is able to see real number of posts in this topic
            $replies = $topic->replies_real;
        }

        // user wants to show certain post. we need to calculate page number based on post id.
        if ($request->has('p')) {
            $page = $this->post->getPage(min(2147483647, (int) $request->get('p')), $topic->id, $perPage);
        }

        // build "more like this" block. it's important to send elasticsearch query before
        // send SQL query to database because search() method exists only in Model and not Builder class.
        $mlt = $this->getCacheFactory()->remember('mlt-post:' . $topic->id, 60 * 24, function () use ($topic) {
            $this->forum->pushCriteria(new OnlyThoseWithAccess());

            $builder = new MoreLikeThisBuilder($topic, $this->forum->pluck('id'));

            // search related topics
            $mlt = $this->topic->search($builder);

            // it's important to reset criteria for the further queries
            $this->forum->resetCriteria();
            return $mlt;
        });

        $this->post->pushCriteria(new ObtainSubscribers($this->userId));

        // magic happens here. get posts for given topic (including first post for every page)
        /* @var \Illuminate\Support\Collection $posts */
        $posts = $this->post->takeForTopic($topic->id, $topic->first_post_id, $page, $perPage);
        $paginate = new LengthAwarePaginator($posts, $replies, $perPage, $page, ['path' => ' ']);

        start_measure('Parsing...');
        $parser = $this->getParsers();

        foreach ($posts as &$post) {
            // parse post or get it from cache
            $post->text = $parser['post']->parse($post->text);

            if ((auth()->guest() || (auth()->check() && $this->auth->allow_sig)) && $post->sig) {
                $post->sig = $parser['sig']->parse($post->sig);
            }

            foreach ($post->comments as &$comment) {
                $comment->text = $parser['comment']->setUserId($comment->user_id)->parse($comment->text);
            }
        }

        stop_measure('Parsing...');

        $postsId = $posts->pluck('id')->toArray();
        $dateTimeString = $posts->last()->created_at->toDateTimeString();

        if ($markTime[Topic::class] < $dateTimeString && $markTime[Forum::class] < $dateTimeString) {
            // mark topic as read
            $topic->markAsRead($dateTimeString, $this->userId, $this->guestId);
            $isUnread = true;

            if ($markTime[Forum::class] < $dateTimeString) {
                $isUnread = $this->topic->isUnread(
                    $forum->id,
                    $markTime[Forum::class],
                    $this->userId,
                    $this->guestId
                );
            }

            if (!$isUnread) {
                $this->forum->markAsRead($forum->id, $this->userId, $this->guestId);
            }
        }

        // create forum list for current user (according to user's privileges)
        $this->pushForumCriteria();

        $treeBuilder = new TreeBuilder();
        $forumList = $treeBuilder->listBySlug($this->forum->list());

        $this->breadcrumb->push($topic->subject, route('forum.topic', [$forum->slug, $topic->id, $topic->slug]));

        $flags = $activities = [];

        if ($this->gate->allows('delete', $forum) || $this->gate->allows('move', $forum)) {
            $reasonList = Reason::pluck('name', 'id')->toArray();

            if ($this->gate->allows('delete', $forum)) {
                $flags = $this->getFlags($postsId);
                $activities = $this->getActivities($postsId);
            }

            $this->forum->skipCriteria(true);
            $adminForumList = $treeBuilder->listBySlug($this->forum->list());
        }

        // informacje o powodzie zablokowania watku, przeniesienia itp
        $warnings = $this->getWarnings($topic);

        $form = $this->getForm($forum, $topic);

        return $this->view(
            'forum.topic',
            compact('posts', 'forum', 'topic', 'paginate', 'forumList', 'adminForumList', 'reasonList', 'form', 'mlt', 'flags', 'warnings', 'activities')
        )->with([
            'markTime'      => $markTime[Topic::class] ? $markTime[Topic::class] : $markTime[Forum::class],
            'subscribers'   => $this->userId ? $topic->subscribers()->pluck('topic_id', 'user_id') : []
        ]);
    }

    /**
     * @return ParserInterface[]
     */
    private function getParsers()
    {
        return [
            'post'      => app('parser.post'),
            'comment'   => app('parser.comment'),
            'sig'       => app('parser.sig')
        ];
    }

    /**
     * @param array $postsId
     * @return mixed
     */
    private function getFlags($postsId)
    {
        return $this->getFlagFactory()->takeForPosts($postsId);
    }

    /**
     * @param int[] $postsId
     * @return array
     */
    private function getActivities($postsId)
    {
        $activities = [];

        // here we go. if user has delete ability, for sure he/she would like to know
        // why posts were deleted and by whom
        $collection = $this->findByObject('post', $postsId, 'delete');

        foreach ($collection->sortByDesc('created_at')->groupBy('object.id') as $row) {
            $activities[$row->first()['object.id']] = $row->first();
        }

        return $activities;
    }

    /**
     * @param \Coyote\Topic $topic
     * @return array
     */
    private function getWarnings($topic)
    {
        $warnings = [];

        // if topic is locked we need to fetch information when and by whom
        if ($topic->is_locked) {
            $warnings['lock'] = $this->findByObject('topic', $topic->id, 'lock')->last();
        }

        if ($topic->prev_forum_id) {
            $warnings['move'] = $this->findByObject('topic', $topic->id, 'move')->last();
        }

        return $warnings;
    }

    /**
     * @param \Coyote\Topic $topic
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function subscribe($topic)
    {
        $subscriber = $topic->subscribers()->forUser($this->userId)->first();

        if ($subscriber) {
            $subscriber->delete();
        } else {
            $topic->subscribers()->create(['user_id' => $this->userId]);
        }

        return response($topic->subscribers()->count());
    }

    /**
     * @param $id
     * @param User $user
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function prompt($id, User $user, Request $request)
    {
        $this->validate($request, ['q' => 'username']);
        $usersId = [];

        $posts = $this->post->findAllBy('topic_id', $id, ['id', 'user_id']);
        $posts->load('comments'); // load comments assigned to posts

        foreach ($posts as $post) {
            if ($post->user_id) {
                $usersId[] = $post->user_id;
            }

            foreach ($post->comments as $comment) {
                if ($comment->user_id) {
                    $usersId[] = $comment->user_id;
                }
            }
        }

        return view('components.prompt')->with('users', $user->lookupName($request['q'], array_unique($usersId)));
    }

    /**
     * @param \Coyote\Topic $topic
     */
    public function mark($topic)
    {
        // pobranie daty i godziny ostatniego razy gdy uzytkownik przeczytal to forum
        $forumMarkTime = $topic->forum->markTime($this->userId, $this->guestId);

        // mark topic as read
        $topic->markAsRead($topic->last_post_created_at, $this->userId, $this->guestId);
        $isUnread = $this->topic->isUnread($topic->forum_id, $forumMarkTime, $this->userId, $this->guestId);

        if (!$isUnread) {
            $this->forum->markAsRead($topic->forum_id, $this->userId, $this->guestId);
        }
    }

    /**
     * @param string $object
     * @param $id
     * @param string $verb
     * @return mixed
     */
    protected function findByObject($object, $id, $verb)
    {
        return app(StreamRepository::class)->findWhere(
            ['object.objectType' => $object, 'object.id' => $id, 'verb' => $verb]
        );
    }
}
