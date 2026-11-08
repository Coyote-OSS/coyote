<?php
namespace Coyote\Http\Controllers;

use Coyote\Activity;
use Coyote\Domain\DiscreetDate;
use Coyote\Domain\Homepage\TopicSummaries;
use Coyote\Domain\Online\CommunityMembers;
use Coyote\Domain\Online\Viewers;
use Coyote\Http\Resources\ActivityFeedResource;
use Coyote\Http\Resources\ActivityResource;
use Coyote\Http\Resources\Api\MicroblogResource;
use Coyote\Http\Resources\FlagResource;
use Coyote\Http\Resources\MicroblogCollection;
use Coyote\Microblog;
use Coyote\Post;
use Coyote\Repositories\Criteria\Forum\OnlyThoseWithAccess as OnlyThoseForumsWithAccess;
use Coyote\Repositories\Criteria\Forum\SkipHiddenCategories;
use Coyote\Repositories\Criteria\SkipIncognitoUsers;
use Coyote\Repositories\Criteria\Topic\OnlyThoseWithAccess as OnlyThoseTopicsWithAccess;
use Coyote\Repositories\Eloquent\ActivityRepository;
use Coyote\Repositories\Eloquent\ReputationRepository;
use Coyote\Repositories\Eloquent\TopicRepository;
use Coyote\Reputation;
use Coyote\Services\Flags;
use Coyote\Services\Forum\UserDefined;
use Coyote\Services\Microblogs\Builder;
use Coyote\Services\Parser\Extensions\Emoji;
use Coyote\Services\Session\Renderer;
use Coyote\User;
use Illuminate\Contracts\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Modules\Campaigns;

class HomeController extends Controller {
    public function __construct(
        private ReputationRepository $reputation,
        private ActivityRepository   $activity,
        private TopicRepository      $topic,
    ) {
        parent::__construct();
    }

    public function index(Campaigns\ForCampaignBanners $presenter): View {
        $this->topic->pushCriteria(new OnlyThoseTopicsWithAccess());
        $this->topic->pushCriteria(new SkipHiddenCategories($this->userId));
        $this->activity->pushCriteria(new OnlyThoseForumsWithAccess($this->auth));
        $this->activity->pushCriteria(new SkipHiddenCategories($this->userId));
        $this->activity->pushCriteria(new SkipIncognitoUsers());
        /** @var Renderer $renderer */
        $renderer = app(Renderer::class);
        $viewers = $renderer->sessionViewers('/');
        $common = [
            'flags'                         => $this->flags(),
            'microblogs'                    => $this->getMicroblogs(),
            'interesting'                   => $this->topic->interesting(),
            'newest'                        => $this->topic->newest(),
            'reputation'                    => $this->reputation(),
            'emojis'                        => Emoji::all(),
            'events'                        => [],
            'settings'                      => $this->getSettings(),
            'home_ads'                      => $this->userIncludeAds(),
            'campaign_banners_home_desktop' => $presenter->bannerSet(Campaigns\DeviceType::Desktop),
            'campaign_banners_home_mobile'  => $presenter->bannerSet(Campaigns\DeviceType::Mobile),
        ];
        if (Gate::any(['alpha-access', 'beta-access'])) {
            /** @var TopicSummaries $summaries */
            $summaries = app(TopicSummaries::class);
            $interestingIds = $common['interesting']->pluck('id')->all();
            return $this->view('homeModern', [
                ...$common,
                'topicLikes'        => $summaries->likes([...$interestingIds, ...$common['newest']->pluck('id')->all()]),
                'topicParticipants' => $summaries->participants($interestingIds, limit:5),
                'activities' => $this->activityFeed(),
                'community'  => $this->community($viewers),
            ]);
        }
        return $this->view('home', [
            ...$common,
            'activities'      => $this->getActivities(),
            'globalViewers'   => $this->globalViewers(),
            'homepageMembers' => $this->members($viewers),
        ]);
    }

    private function reputation(): array {
        $cache = app(Cache\Repository::class);
        $date = new DiscreetDate(date('Y-m-d H:i:s'));
        return $cache->remember('homepage:reputation', 30 * 60, fn() => [
            'week'    => $this->reputation->reputationSince($date->startOfThisWeek(), limit:5),
            'month'   => $this->reputation->reputationSince($date->startOfThisMonth(), limit:5),
            'quarter' => $this->reputation->reputationSince($date->startOfThisQuarter(), limit:5),
        ]);
    }

    private function community(Viewers $viewers): array {
        /** @var CommunityMembers $members */
        $members = app(CommunityMembers::class);
        return [
            ...$members->members($viewers, onlineLimit:50, recentLimit:35, followedIds:$this->relatedUsers(blocked:false)),
            'onlineCount' => \count($viewers->users),
            'guestsCount' => $viewers->guestsCount,
        ];
    }

    private function members(Viewers $viewers): array {
        return [
            'usersTotal'   => \number_format(User::query()->withTrashed()->count(), thousands_separator:'.'),
            'usersOnline'  => \count($viewers->users) + $viewers->guestsCount,
            'guestsOnline' => $viewers->guestsCount,
        ];
    }

    private function getMicroblogs(): array {
        /** @var Builder $builder */
        $builder = app(Builder::class);
        $microblogs = $builder->orderByScore()->popular();
        MicroblogResource::withoutWrapping();
        return (new MicroblogCollection($microblogs))->resolve($this->request);
    }

    private function getActivities(): array {
        return ActivityResource::collection($this->activity->latest(25))->toArray($this->request);
    }

    private function activityFeed(): array {
        $result = $this->activity->latest(25)
            ->filter(fn(Activity $activity) => $activity->content && !$activity->content->trashed()
                && $activity->topic && !$activity->topic->trashed())
            ->values();
        $result->loadMorphCount('content', [Post::class => ['comments']]);
        $activities = ActivityFeedResource::collection($result)->toArray($this->request);
        [$votedPosts, $votedComments] = $this->votedBy($activities);
        $blockedUsers = $this->relatedUsers(blocked:true);
        foreach ($activities as &$activity) {
            $voted = $activity['vote']['type'] === 'post' ? $votedPosts : $votedComments;
            $activity['voted'] = \in_array($activity['vote']['id'], $voted);
            $activity['blocked'] = $activity['user_id'] && \in_array($activity['user_id'], $blockedUsers);
        }
        return $activities;
    }

    private function globalViewers(): View {
        /** @var Renderer $renderer */
        $renderer = app(Renderer::class);
        return $renderer->render('/', local:false, includeHeading:false);
    }

    /**
     * Users blocked, or followed, by the current user.
     * @return int[]
     */
    private function relatedUsers(bool $blocked): array {
        if ($this->userId === null) {
            return [];
        }
        /** @var UserDefined $userDefined */
        $userDefined = app(UserDefined::class);
        $relations = \array_filter($userDefined->followers($this->auth), fn(array $relation) => $relation['is_blocked'] == $blocked);
        return \array_column($relations, 'user_id');
    }

    private function votedBy(array $activities): array {
        if ($this->userId === null) {
            return [[], []];
        }
        $ids = fn(string $type) => \array_column(\array_column(
            \array_filter($activities, fn(array $activity) => $activity['vote']['type'] === $type),
            'vote'), 'id');
        return [
            Post\Vote::query()
                ->where('user_id', $this->userId)
                ->whereIn('post_id', $ids('post'))
                ->pluck('post_id')
                ->all(),
            DB::table('post_comment_votes')
                ->where('user_id', $this->userId)
                ->whereIn('post_comment_id', $ids('comment'))
                ->pluck('post_comment_id')
                ->all(),
        ];
    }

    private function flags(): array {
        /** @var Flags $flags */
        $flags = app(Flags::class);
        $resourceFlags = $flags
            ->fromModels([Microblog::class])
            ->permission('microblog-delete')
            ->get();
        return FlagResource::collection($resourceFlags)->toArray($this->request);
    }

    private function userIncludeAds(): bool {
        /** @var User|null $user */
        $user = auth()->user();
        if ($user === null) {
            return true;
        }
        return $user->reputation < Reputation::NO_ADS;
    }
}
