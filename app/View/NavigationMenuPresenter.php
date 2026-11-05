<?php
namespace Coyote\View;

use Carbon\Carbon;
use Coyote\Forum;
use Coyote\Services\Guest;
use Coyote\Services\UrlBuilder;
use Coyote\Topic;
use Coyote\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;

readonly class NavigationMenuPresenter {
    public function __construct(private Guest $guest) {}

    public function navigationForumMenu(NavigationMenu $menu): array {
        return [
            'categories'        => $this->categories(),
            'topics'            => $this->topics(),
            'allCategoriesHref' => route('forum.categories'),
            'allTopicsHref'     => route('forum.all'),
            'headerItems'       => $this->headerItems($menu),
            'userFilters'       => $this->userFilters($menu),
        ];
    }

    private function categories(): array {
        $forums = $this->forums();
        $children = $forums->whereNotNull('parent_id')->groupBy('parent_id');
        return $forums
            ->whereNull('parent_id')
            ->filter(fn(Forum $forum): bool => !$forum->is_hidden)
            ->map(function (Forum $forum) use ($children): array {
                /** @var Collection<Forum> $subForums */
                $subForums = $children->get($forum->id, collect());
                return [
                    'title'  => $forum->name,
                    'href'   => $this->categoryHref($forum->slug),
                    'count'  => $this->formatCount($forum->posts + $subForums->sum('posts')),
                    'isRead' => $this->isRead($forum) && $subForums->every(fn(Forum $child) => $this->isRead($child)),
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * @return Collection<Forum>
     */
    private function forums(): Collection {
        $userId = auth()->id();
        return Forum::query()
            ->select(['forums.id', 'forums.parent_id', 'forums.name', 'forums.slug', 'forums.posts'])
            ->addSelect(['last_post_created_at' => fn(QueryBuilder $query) => $query
                ->select('posts.created_at')
                ->from('posts')
                ->whereColumn('posts.id', 'forums.last_post_id')])
            ->withForumMarkTime($this->guest->guestId)
            ->whereNull('forums.url')
            ->where(fn(Builder $query) => $this->onlyWithAccess($query, auth()->user()))
            ->when($userId !== null,
                fn(Builder $query) => $query
                    ->addSelect('forum_orders.is_hidden')
                    ->leftJoin('forum_orders', fn(JoinClause $join) => $join
                        ->on('forum_orders.forum_id', '=', 'forums.id')
                        ->where('forum_orders.user_id', '=', $userId))
                    ->orderByRaw('COALESCE(forum_orders.order, forums.order)'),
                fn(Builder $query) => $query->orderBy('forums.order'))
            ->get();
    }

    private function onlyWithAccess(Builder $query, ?User $user): void {
        $query->where('forums.is_prohibited', false);
        if ($user !== null) {
            $groupIds = $user->groups()->pluck('id')->toArray();
            if (!empty($groupIds)) {
                $query->orWhereExists(fn(QueryBuilder $sub) => $sub
                    ->select('forum_id')
                    ->from('forum_access')
                    ->whereIn('group_id', $groupIds)
                    ->whereColumn('forum_access.forum_id', 'forums.id'));
            }
        }
    }

    private function isRead(Forum $forum): bool {
        if ($forum->last_post_created_at === null) {
            return true;
        }
        $lastPostAt = Carbon::parse($forum->last_post_created_at);
        if ($forum->read_at === null) {
            if ($this->guest->guestId === null) {
                return true;
            }
            return $this->guest->getDefaultSessionTime() > $lastPostAt;
        }
        return $forum->read_at >= $lastPostAt;
    }

    private function topics(): array {
        return [
            $this->topic(233131, 'Ile zarabiacie?', 'Porównaj swoje zarobki'),
            $this->topic(355933, 'Krajowy system e-Faktur', 'KSeF w praktyce'),
            $this->topic(372593, 'Koniec Eldorado w IT', 'Forum / Kariera'),
            $this->topic(364478, 'Zwolnienia w IT', 'Sytuacja na rynku pracy'),
            $this->topic(141606, 'Programistyczne WTF jakie Was spotkały', 'Kod, który boli'),
            $this->topic(44030, 'trochę humoru... :-)', 'Śmieszki z IT'),
        ];
    }

    private function topic(int $topicId, string $title, string $subtitle): array {
        /** @var Topic|null $topic */
        $topic = Topic::query()->find($topicId);
        return [
            'title'    => $title,
            'subtitle' => $subtitle,
            'href'     => $topic ? UrlBuilder::post($topic->lastPost) : '/',
            'count'    => $this->formatCount($topic?->replies ?? 0),
        ];
    }

    private function headerItems(NavigationMenu $menu): array {
        return [
            ['title' => "$menu->activeDiscussions aktywnych dyskusji dzisiaj",
             'icon'  => 'navigationActiveDiscussions',
             'help'  => 'Wątki aktywne w ciągu ostatnich 24 godzin.'],
            ['title'  => "$menu->usersOnline online",
             'icon'   => 'navigationOnlineUsers',
             'online' => true,
             'help'   => 'Użytkownicy aktywni w ciągu ostatnich 60 sekund.'],
        ];
    }

    private function userFilters(NavigationMenu $menu): array {
        if (!$menu->includeUserFilters) {
            return [];
        }
        return [
            ['title' => 'Wszystkie', 'href' => route('forum.all')],
            ['title' => 'Obserwowane', 'href' => route('forum.subscribes')],
            ['title' => 'Biorę udział', 'href' => route('forum.mine'), 'help' => 'Wątki w których brałem udział.'],
        ];
    }

    private function formatCount(int $postsCount): array {
        $format = new FormatNumber();
        return [
            'long'  => $format->formatLong($postsCount) . ' postów',
            'short' => $format->formatShort($postsCount),
        ];
    }

    private function categoryHref(string $categorySlug): string {
        return route('forum.category', [$categorySlug]);
    }
}
