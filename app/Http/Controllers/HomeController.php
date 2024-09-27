<?php
namespace Coyote\Http\Controllers;

use Coyote\Domain\DiscreetDate;
use Coyote\Http\Resources\ActivityResource;
use Coyote\Http\Resources\Api\MicroblogResource;
use Coyote\Http\Resources\FlagResource;
use Coyote\Http\Resources\MicroblogCollection;
use Coyote\Microblog;
use Coyote\Repositories\Contracts\ActivityRepositoryInterface as ActivityRepository;
use Coyote\Repositories\Contracts\TopicRepositoryInterface as TopicRepository;
use Coyote\Repositories\Criteria\Forum\OnlyThoseWithAccess as OnlyThoseForumsWithAccess;
use Coyote\Repositories\Criteria\Forum\SkipHiddenCategories;
use Coyote\Repositories\Criteria\Topic\OnlyThoseWithAccess as OnlyThoseTopicsWithAccess;
use Coyote\Repositories\Eloquent\ReputationRepository;
use Coyote\Services\Flags;
use Coyote\Services\Microblogs\Builder;
use Coyote\Services\Parser\Extensions\Emoji;
use Coyote\Services\Session\Renderer;
use Illuminate\Contracts\Cache;
use Illuminate\View\View;
use Neon\Domain;
use Neon\StaticEvents;
use Neon\View\Components;
use Neon\View\Language\Polish;

class HomeController extends Controller
{
    public function __construct(
        private ReputationRepository $reputation,
        private ActivityRepository   $activity,
        private TopicRepository      $topic,
    )
    {
        parent::__construct();
    }

    public function index(): View
    {
        $cache = app(Cache\Repository::class);
        $this->topic->pushCriteria(new OnlyThoseTopicsWithAccess());
        $this->topic->pushCriteria(new SkipHiddenCategories($this->userId));
        $date = new DiscreetDate(date('Y-m-d H:i:s'));
        return $this->view('home', [
            'flags'       => $this->flags(),
            'microblogs'  => $this->getMicroblogs(),
            'interesting' => $this->topic->interesting(),
            'newest'      => $this->topic->newest(),
            'activities'  => $this->getActivities(),
            'reputation'  => $cache->remember('homepage:reputation', 30 * 60, fn() => [
                'week'    => $this->reputation->reputationSince($date->startOfThisWeek(), limit:5),
                'month'   => $this->reputation->reputationSince($date->startOfThisMonth(), limit:5),
                'quarter' => $this->reputation->reputationSince($date->startOfThisQuarter(), limit:5),
            ]),
            'emojis'      => Emoji::all(),
            'events'      => \array_map(
                fn(Domain\Event\Event $event) => new Components\Event\Event(new Polish(), $event),
                (new StaticEvents())->fetchEvents(),
            ),

            'globalViewers' => $this->globalViewers(),
        ])
            ->with('settings', $this->getSettings());
    }

    private function getMicroblogs(): array
    {
        /** @var Builder $builder */
        $builder = app(Builder::class);
        $microblogs = $builder->orderByScore()->popular();
        MicroblogResource::withoutWrapping();
        return (new MicroblogCollection($microblogs))->resolve($this->request);
    }

    private function getActivities(): array
    {
        $this->activity->pushCriteria(new OnlyThoseForumsWithAccess($this->auth));
        $this->activity->pushCriteria(new SkipHiddenCategories($this->userId));
        $result = $this->activity->latest(20);
        return ActivityResource::collection($result)->toArray($this->request);
    }

    private function globalViewers(): View
    {
        /** @var Renderer $viewers */
        $viewers = app(Renderer::class);
        return $viewers->render('Użytkownicy online', requestUri:null);
    }

    private function flags(): array
    {
        /** @var Flags $flags */
        $flags = app(Flags::class);
        $resourceFlags = $flags
            ->fromModels([Microblog::class])
            ->permission('microblog-delete')
            ->get();
        return FlagResource::collection($resourceFlags)->toArray($this->request);
    }
}
