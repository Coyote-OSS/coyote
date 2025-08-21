<?php

namespace Coyote\Providers;

use Coyote\Guide;
use Coyote\Microblog;
use Coyote\Post;
use Coyote\Repositories\Contracts\BlockRepositoryInterface;
use Coyote\Repositories\Contracts\FirewallRepositoryInterface;
use Coyote\Repositories\Contracts\ForumRepositoryInterface;
use Coyote\Repositories\Contracts\GroupRepositoryInterface;
use Coyote\Repositories\Contracts\JobRepositoryInterface;
use Coyote\Repositories\Contracts\MicroblogRepositoryInterface;
use Coyote\Repositories\Contracts\PastebinRepositoryInterface;
use Coyote\Repositories\Contracts\PaymentRepositoryInterface;
use Coyote\Repositories\Contracts\PmRepositoryInterface;
use Coyote\Repositories\Contracts\PostRepositoryInterface;
use Coyote\Repositories\Contracts\TagRepositoryInterface;
use Coyote\Repositories\Contracts\UserRepositoryInterface;
use Coyote\Repositories\Contracts\WikiRepositoryInterface;
use Coyote\Topic;
use Coyote\User;
use Illuminate\Routing\Router;

class RouteServiceProvider extends \Illuminate\Foundation\Support\Providers\RouteServiceProvider
{
    /** @var string */
    protected $namespace = 'Coyote\Http\Controllers';
    /** @var Router */
    protected $router;

    public function boot(): void
    {
        $this->router->pattern('id', '[0-9]+');
        $this->router->pattern('wiki', '[0-9]+');
        $this->router->pattern('block', '[0-9]+');
        $this->router->pattern('group', '[0-9]+');
        $this->router->pattern('firewall', '[0-9]+');
        $this->router->pattern('pastebin', '[0-9]+');
        $this->router->pattern('microblog', '[0-9]+');
        $this->router->pattern('any_microblog', '[0-9]+');
        $this->router->pattern('topic', '[0-9]+');
        $this->router->pattern('topic_trashed', '[0-9]+');
        $this->router->pattern('user', '[0-9]+');
        $this->router->pattern('user_trashed', '[0-9]+');
        $this->router->pattern('post', '[0-9]+');
        $this->router->pattern('job', '[0-9]+');
        $this->router->pattern('pm', '[0-9]+');
        $this->router->pattern('asset', '[0-9]+');
        $this->router->pattern('guide', '[0-9]+');
        $this->router->pattern('payment', '[0-9a-z\-]+');
        $this->router->pattern('banner', '[0-9a-f]{8}\b-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-\b[0-9a-f]{12}');

        $this->router->pattern('forum', '[A-Za-ząęśćłźżóń\-\_\/\.\+]+');
        $this->router->pattern('tag_name', '([a-ząęśżźćółń0-9\-\.\#\+])+');
        $this->router->pattern('slug', '.*');
        $this->router->pattern('path', '.*'); // being used on wiki routes
        $this->router->pattern('tab', 'Reputation|Microblog'); // user's profile tabs

        $this->router->model('user', UserRepositoryInterface::class);
        $this->router->model('post', PostRepositoryInterface::class);
        $this->router->model('pastebin', PastebinRepositoryInterface::class);
        $this->router->model('microblog', MicroblogRepositoryInterface::class);
        $this->router->model('wiki', WikiRepositoryInterface::class);
        $this->router->model('pastebin', PastebinRepositoryInterface::class);
        $this->router->model('firewall', FirewallRepositoryInterface::class);
        $this->router->model('group', GroupRepositoryInterface::class);
        $this->router->model('block', BlockRepositoryInterface::class);
        $this->router->model('job', JobRepositoryInterface::class);
        $this->router->model('payment', PaymentRepositoryInterface::class);
        $this->router->model('tag', TagRepositoryInterface::class);
        $this->router->model('pm', PmRepositoryInterface::class);
        $this->router->model('guide', Guide::class);

        $this->router->bind('forum', function ($slug) {
            return $this->app->make(ForumRepositoryInterface::class, [$this->app])->where('slug', $slug)->firstOrFail();
        });

        // we use model instead of repository to avoid putting global criteria to all methods in repository
        $this->router->bind('user_trashed', fn($id) => User::withTrashed()->findOrFail($id));

        // we use model instead of repository to avoid putting global criteria to all methods in repository
        $this->router->bind('post_trashed', fn($id) => Post::withTrashed()->findOrFail($id));
        $this->router->bind('comment_trashed', fn($id) => Post\Comment::withTrashed()->findOrFail($id));

        // we use model instead of repository to avoid putting global criteria to all methods in repository
        $this->router->bind('topic_trashed', fn($id) => Topic::withTrashed()->findOrFail($id));

        $this->router->bind('topic', function ($id) {
            $user = $this->getCurrentRequest()->user();
            if ($this->router->currentRouteName() === 'forum.topic' && $user && $user->can('forum-delete')) {
                return Topic::withTrashed()->findOrFail($id);
            }
            return Topic::findOrFail($id);
        });

        $this->router->bind('any_microblog', fn($id) => Microblog::query()->withoutGlobalScopes()->findOrFail($id));

        parent::boot();
    }

    public function register(): void
    {
        parent::register();
        $this->router = $this->app->make(Router::class);
    }

    public function map(): void
    {
        $this->mapApiRoutes();
        $this->mapWebRoutes();
    }

    private function mapWebRoutes(): void
    {
        $this->router->group([
            'namespace'  => $this->namespace,
            'middleware' => 'web',
        ], function () {
            require base_path('routes/auth.php');
            require base_path('routes/chat.php');
            require base_path('routes/misc.php');
            require base_path('routes/forum.php');
            require base_path('routes/job.php');
            require base_path('routes/microblog.php');
            require base_path('routes/user.php');
            require base_path('routes/profile.php');
            require base_path('routes/pastebin.php');
            require base_path('routes/adm.php');
            require base_path('routes/guide.php');
            require base_path('routes/comment.php');
            require base_path('routes/wiki.php'); // must be at the end
        });
    }

    private function mapApiRoutes(): void
    {
        $this->router->group([
            'namespace'  => $this->namespace,
            'middleware' => 'api',
            'domain'     => $this->app->runningUnitTests() ? '' : config('services.api.host'),
        ], function () {
            require base_path('routes/api.php');
        });
    }
}
