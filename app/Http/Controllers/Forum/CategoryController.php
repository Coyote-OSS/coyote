<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Events\UserSaved;
use Coyote\Http\Resources\Api\ForumCollection;
use Coyote\Http\Resources\FlagResource;
use Coyote\Http\Resources\TopicCollection;
use Coyote\Repositories\Criteria\Topic\BelongsToForum;
use Coyote\Repositories\Criteria\Topic\StickyGoesFirst;
use Coyote\Services\Flags;
use Coyote\Services\Forum\TreeBuilder\Builder;
use Coyote\Services\Forum\TreeBuilder\ListDecorator;
use Coyote\Services\Guest;
use Coyote\Topic;
use Illuminate\Http\Request;

class CategoryController extends BaseController
{
    /**
     * @param \Coyote\Forum $forum
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index($forum, Request $request)
    {
        $this->pushForumCriteria();

        $forumList = (new ListDecorator(new Builder($this->forum->list())))->build();

        $forums = $this
            ->forum
            ->categories($this->guestId, $forum->id)
            ->mapCategory($this->guestId);

        $forums = ForumCollection::factory($forums)->setParentId($forum->id);

        // display topics for this category
        $this->topic->pushCriteria(new BelongsToForum($forum->id));
        $this->topic->pushCriteria(new StickyGoesFirst());

        // get topics according to given criteria
        $paginate = $this
            ->topic
            ->lengthAwarePagination(
                $this->userId,
                $this->guestId,
                'topics.last_post_id',
                'DESC',
                $this->topicsPerPage($request)
            )
            ->appends($request->except('page'));

        $flags = resolve(Flags::class)->fromModels([Topic::class])->permission('delete', [$forum])->get();
        $flags = FlagResource::collection($flags);

        $guest = new Guest($this->guestId);

        $topics = (new TopicCollection($paginate))
            ->setGuest($guest)
            ->setRepository($this->topic);

        $collapse = $this->collapse();
        $postsPerPage = $this->postsPerPage($this->request);

        return $this->view('forum.category')->with(
            compact('forumList', 'forum', 'topics', 'forums', 'collapse', 'flags', 'postsPerPage')
        );
    }

    /**
     * @param \Coyote\Forum $forum
     */
    public function mark($forum)
    {
        $forum->markAsRead($this->guestId);
        $this->topic->flushRead($forum->id, $this->guestId);

        $forums = $this->forum->where('parent_id', $forum->id)->get();

        foreach ($forums as $forum) {
            $forum->markAsRead($this->guestId);
            $this->topic->flushRead($forum->id, $this->guestId);
        }
    }

    /**
     * Set category visibility
     *
     * @param \Coyote\Forum $forum
     */
    public function collapseSection($forum)
    {
        $collapse = $this->getSetting('forum.collapse');
        if ($collapse !== null) {
            $collapse = unserialize($collapse);
        }

        $collapse[$forum->id] = (int) !($collapse[$forum->id] ?? false);
        $this->setSetting('forum.collapse', serialize($collapse));
    }

    /**
     * @param Request $request
     * @throws \Illuminate\Validation\ValidationException
     */
    public function setup(Request $request)
    {
        $this->validate($request, ['*.order' => 'required|int', '*.id' => 'required|int']);

        $this->pushForumCriteria();

        $categories = $this
            ->forum
            ->categories($this->guestId)
            ->filter(function ($item) {
                return $item->parent_id === null;
            });

        $input = $request->input();
        $result = [];

        foreach ($categories as &$category) {
            foreach ($input as $row) {
                if ($category->id === $row['id']) {
                    $category->order = $row['order'];
                    $category->is_hidden = $row['is_hidden'];
                }
            }

            $result[] = $category->only(['order']) + ['forum_id' => $category->id, 'is_hidden' => $category->is_hidden ?? false];
        }

        $this->transaction(function () use ($result) {
            $this->forum->setup($this->userId, $result);
        });

        event(new UserSaved($this->auth));
    }
}
