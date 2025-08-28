<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Http\Controllers\RenderParams;
use Coyote\Http\Resources\FlagResource;
use Coyote\Http\Resources\ForumCollection;
use Coyote\Http\Resources\TopicCollection;
use Coyote\Repositories\Contracts\UserRepositoryInterface;
use Coyote\Repositories\Criteria\SkipIncognitoTopicAuthors;
use Coyote\Repositories\Criteria\Topic\OnlyMine;
use Coyote\Repositories\Criteria\Topic\OnlyThoseWithAccess;
use Coyote\Repositories\Criteria\Topic\SkipForum;
use Coyote\Repositories\Criteria\Topic\SkipLockedCategories;
use Coyote\Repositories\Criteria\Topic\Subscribes;
use Coyote\Repositories\Criteria\WithTags;
use Coyote\Repositories\Criteria\WithTrashed;
use Coyote\Repositories\Eloquent\ForumRepository;
use Coyote\Repositories\Eloquent\PostRepository;
use Coyote\Repositories\Eloquent\TagRepository;
use Coyote\Repositories\Eloquent\TopicRepository;
use Coyote\Services\Flags;
use Coyote\Services\Guest;
use Coyote\Topic;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Lavary;
use Lavary\Menu\Builder;
use Lavary\Menu\Item;
use Lavary\Menu\Menu;

class HomeController extends BaseController {
    private ?Builder $tabs;

    public function __construct(
        ForumRepository $forum,
        TopicRepository $topic,
        PostRepository  $post,
        TagRepository   $tag,
    ) {
        parent::__construct($forum, $topic, $post, $tag);
        /** @var Menu $app */
        $app = app(Menu::class);
        $this->tabs = $app->make('_forum', function (Builder $menu) {
            foreach (config('laravel-menu._forum') as $title => $row) {
                $data = array_pull($row, 'data');
                $item = $menu->add($title, $row);
                $item->link->attr(['class' => 'nav-link']);
                $item->data($data);
            }
        });

        $this->middleware(function (Request $request, $next) {
            $this->tabs->filter(function (Item $item) {
                if ($item->data('role') === true) {
                    return $this->userId !== null;
                }

                return true;
            });

            // currently selected tab
            [, $suffix] = explode('.', $request->route()->getName());

            if (in_array($suffix, ['categories', 'all', 'subscribes', 'mine', 'interesting'])) {
                $this->setSetting('forum.tab', $suffix);
            }

            return $next($request);
        });
    }

    /**
     * @param string $view
     * @param array $data
     * @return View
     */
    protected function view($view = null, $data = []) {
        [, $suffix] = explode('.', $this->request->route()->getName());

        $currentTab = $suffix == 'home' ? $this->getSetting('forum.tab', 'categories') : $suffix;
        $title = null;

        foreach ($this->tabs->all() as $tab) {
            if ("forum.$currentTab" == $tab->link->path['route']) {
                $tab->activate();

                $title = $tab->title;
            }
        }

        return parent::view($view, $data)->with([
            'title'     => $currentTab != 'categories' ? $title : '',
            'forumTabs' => collect($this->tabs->all())
                ->map(function (Lavary\Menu\Item $menuItem): array {
                    return [
                        'label'    => $menuItem->title,
                        'selected' => $menuItem->active,
                        'href'     => $menuItem->url(),
                    ];
                }),
        ]);
    }

    /**
     * @return View
     */
    public function index() {
        $tab = $this->getSetting('forum.tab', 'categories');

        return $this->{$tab}();
    }

    public function categories(): View {
        $this->pushForumCriteria();
        $forums = $this
            ->forum
            ->categories($this->guestId)
            ->mapCategory();
        return $this->view('forum.home', [
            'forums'       => ForumCollection::factory($forums),
            'collapse'     => $this->collapse(),
            'topicsTotal'  => Topic::query()->count(),
            'postsPerPage' => $this->postsPerPage($this->request),
        ]);
    }

    public function all(): View {
        $this->topic->pushCriteria(new SkipLockedCategories());
        $this->topic->pushCriteria(new SkipIncognitoTopicAuthors(auth()->id()));
        return $this->loadAndRender();
    }

    public function mine(): View {
        if ($this->userId) {
            $this->topic->pushCriteria(new OnlyMine($this->userId, false));
        }
        $topics = $this->load();
        return $this->render($topics)->with('user_id', $this->userId);
    }

    /**
     * @param int $userId
     * @return View
     */
    public function user(int $userId) {
        $this->topic->pushCriteria(new OnlyMine($userId, true));
        $topics = $this->load();

        $user = app(UserRepositoryInterface::class)->pushCriteria(new WithTrashed())->find($userId);
        abort_if(is_null($user), 404);

        if ($this->request->route()->getName() == 'forum.user') {
            $item = $this->tabs->add('Posty: ' . $user->name, ['route' => ['forum.user', $userId], 'class' => 'nav-item']);

            $item->link->attr(['class' => 'nav-link']);
            $item->activate();
        }

        return $this->render($topics)->with('user_id', $userId);
    }

    /**
     * @return View
     */
    public function subscribes() {
        $this->topic->pushCriteria(new Subscribes($this->userId));

        return $this->loadAndRender();
    }

    public function tag(string $name): View {
        $item = $this
            ->tabs
            ->add('Wątki z: ' . $name, [
                'route' => [
                    'forum.tag',
                    \urlEncode($this->request->route('tag_name')),
                ],
                'class' => 'nav-item',
            ]);

        $item->link->attr(['class' => 'nav-link']);
        $item->activate();

        $this->topic->pushCriteria(new WithTags($name));

        return $this->loadAndRender(new RenderParams($name));
    }

    /**
     * @return View
     */
    public function interesting() {
        $this->topic->pushCriteria(new WithTags(json_decode($this->getSetting('forum.tags', '[]'))));
        return $this->loadAndRender();
    }

    /**
     * Mark ALL categories as READ
     */
    public function mark() {
        $forums = $this->forum->all(['id']);

        /** @var \Coyote\Forum $forum */
        foreach ($forums as $forum) {
            $forum->markAsRead($this->guestId);
            $this->topic->flushRead($forum->id, $this->guestId);
        }
    }

    /**
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    private function load() {
        $this->topic->pushCriteria(new OnlyThoseWithAccess($this->auth));

        // if someone wants to find all user's topics, we can't hide those from our hidden categories.
        if (!str_contains($this->request->route()->getActionName(), '@user')) {
            $this->topic->pushCriteria(new SkipForum($this->forum->findHiddenIds($this->userId)));
        }

        return $this
            ->topic
            ->lengthAwarePagination(
                $this->userId,
                $this->guestId,
                'topics.last_post_id',
                'DESC',
                $this->topicsPerPage($this->request),
            )
            ->appends($this->request->except('page'));
    }

    /**
     * @param \Illuminate\Contracts\Pagination\LengthAwarePaginator $paginate
     * @return View
     */
    private function render($paginate, RenderParams $renderParams = null) {
        $guest = new Guest($this->guestId);

        $topics = (new TopicCollection($paginate))
            ->setGuest($guest)
            ->setRepository($this->topic);

        /** @var Flags $flags */
        $flags = resolve(Flags::class);
        $resourceFlags = $flags
            ->fromModels([Topic::class])
            ->permission('forum-delete')
            ->get();
        return $this->view('forum.topics', [
            'topics'       => $topics,
            'forums'       => [],
            'collapse'     => $this->collapse(),
            'flags'        => FlagResource::collection($resourceFlags),
            'postsPerPage' => $this->postsPerPage($this->request),
            'renderParams' => $renderParams,
        ]);
    }

    /**
     * @return View
     */
    private function loadAndRender(RenderParams $renderParams = null) {
        return $this->render($this->load(), $renderParams);
    }
}
