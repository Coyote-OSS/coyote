<?php
namespace Coyote\Providers;

use Carbon\Carbon;
use Coyote\Domain\OAuth\OAuth;
use Coyote\Forum;
use Coyote\Models\Asset;
use Coyote\Services\Elasticsearch\Api as EsApi;
use Coyote\Services\FormBuilder\FormBuilder;
use Coyote\Services\FormBuilder\FormInterface;
use Coyote\Services\FormBuilder\ValidatesWhenSubmitted;
use Coyote\Services\Forum\Tracker;
use Coyote\Services\Guest;
use Coyote\Services\Invoice;
use Coyote\SocialiteOAuth;
use Coyote\User;
use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Passport\Passport;
use Symfony\Component\Mime\MimeTypes;

class AppServiceProvider extends ServiceProvider {
    public function boot(): void {
        // force HTTPS according to cloudflare HTTP_X_FORWARDED_PROTO header
        $this->app['request']->server->set(
            'HTTPS',
            $this->app['request']->server('HTTP_X_FORWARDED_PROTO') === 'https',
        );

        $this->app['validator']->extend('username', 'Coyote\Http\Validators\UserValidator@validateName');
        $this->app['validator']->extend('user_unique', 'Coyote\Http\Validators\UserValidator@validateUnique');
        $this->app['validator']->extend('user_exist', 'Coyote\Http\Validators\UserValidator@validateExist');
        $this->app['validator']->extend('password', 'Coyote\Http\Validators\PasswordValidator@validatePassword');
        $this->app['validator']->extend('reputation', 'Coyote\Http\Validators\ReputationValidator@validateReputation');
        $this->app['validator']->extend('spam_link', 'Coyote\Http\Validators\SpamValidator@validateSpamLink');
        $this->app['validator']->extend('spam_chinese', 'Coyote\Http\Validators\SpamValidator@validateSpamChinese');
        $this->app['validator']->extend('spam_foreign', 'Coyote\Http\Validators\SpamValidator@validateSpamForeignLink');
        $this->app['validator']->extend('tag', 'Coyote\Http\Validators\TagValidator@validateTag');
        $this->app['validator']->extend('tag_creation', 'Coyote\Http\Validators\TagValidator@validateTagCreation');
        $this->app['validator']->extend('city', 'Coyote\Http\Validators\CityValidator@validateCity');
        $this->app['validator']->extend('wiki_unique', 'Coyote\Http\Validators\WikiValidator@validateUnique');
        $this->app['validator']->extend('wiki_route', 'Coyote\Http\Validators\WikiValidator@validateRoute');
        $this->app['validator']->extend('email_unique', 'Coyote\Http\Validators\EmailValidator@validateUnique');
        $this->app['validator']->extend('email_confirmed', 'Coyote\Http\Validators\EmailValidator@validateConfirmed');
        $this->app['validator']->extend('cc_number', 'Coyote\Http\Validators\CreditCardValidator@validateNumber');
        $this->app['validator']->extend('cc_cvc', 'Coyote\Http\Validators\CreditCardValidator@validateCvc');
        $this->app['validator']->extend('cc_date', 'Coyote\Http\Validators\CreditCardValidator@validateDate');
        $this->app['validator']->extend('host', 'Coyote\Http\Validators\HostValidator@validateHost');

        $this->app['validator']->replacer('reputation', function ($message, $attribute, $rule, $parameters) {
            return str_replace(':point', $parameters[0], $message);
        });

        $this->app['validator']->replacer('spam_link', function ($message, $attribute, $rule, $parameters) {
            return str_replace(':point', $parameters[0], $message);
        });

        $this->app['validator']->replacer('spam_foreign', function ($message, $attribute, $rule, $parameters) {
            return str_replace(':posts', $parameters[0], $message);
        });

        $this->app['validator']->replacer('tag_creation', function ($message, $attribute, $rule, $parameters) {
            return str_replace(':point', $parameters[0], $message);
        });

        $this->app['validator']->replacer('host', function ($message, $attribute, $rule, $parameters) {
            return str_replace(':host', implode(', ', $parameters), $message);
        });

        $this->registerMacros();

        Paginator::useBootstrap();
        MimeTypes::setDefault(new MimeTypes(['text/x-c++' => ['cpp']]));
        Passport::$clientUuids = false;
    }

    /**
     * Register any application services.
     *
     * This service provider is a great spot to register your various container
     * bindings with the application. As you can see, we are registering our
     * "Registrar" implementation here. You can add your own bindings too!
     *
     * @return void
     */
    public function register(): void {
        $this->app->singleton(Guest::class, function ($app) {
            $guest = new Guest($app['session.store']->get('guest_id'));
            $createdAt = $app['session.store']->get('created_at');
            return $guest->setDefaultSessionTime(Carbon::createFromTimestamp($createdAt ?? ''));
        });

        $this->app->bind(EsApi::class, function ($app) {
            return new EsApi($app[Client::class], $app['config']['services']['es']['host'], $app['config']['services']['es']['port']);
        });

        $this->app->singleton('form.builder', function ($app) {
            return new FormBuilder($app);
        });

        $this->app['events']->listen(RouteMatched::class, function () {
            $this->app->resolving(FormInterface::class, function (FormInterface $form, $app) {
                $form->setContainer($app)
                    ->setRedirector($app->make(Redirector::class))
                    ->setRequest($app->make('request'));

                if ($form instanceof ValidatesWhenSubmitted && $form->isSubmitted()) {
                    $form->buildForm();
                    $form->validate();
                }
            });
        });

        $this->app->resolving(Invoice\Pdf::class, function (Invoice\Pdf $pdf, $app) {
            $pdf->setVendor($app['config']->get('vendor'));
        });

        $this->app->singleton(OAuth::class, SocialiteOAuth::class);
    }

    private function registerMacros() {
        Collection::macro('flush', function () {
            $this->items = [];
        });

        Collection::macro('exceptUser', function (User $auth = null) {
            if ($auth === null) {
                return $this;
            }

            return $this->filter(function ($user) use ($auth) {
                return $user !== null && $user->id !== $auth->id;
            });
        });

        Collection::macro('exceptUsers', function ($others = []) {
            if (!($others instanceof Collection)) {
                $others = collect($others);
            }

            if (!count($others)) {
                return $this;
            }

            return $this->filter(function (User $user) use ($others) {
                return !$others->contains('id', $user->id);
            });
        });

        Collection::macro('mapCategory', function () {
            return $this->map(function (Forum $forum) {
                $post = $forum->post;

                if ($post) {
                    $post->topic->setRelation('forum', $forum);
                    $post->setRelation('topic', Tracker::make($post->topic));
                }

                return $forum;
            });
        });

        Collection::macro('groupCategory', function () {
            /** @var \Illuminate\Support\Collection $this */
            $collection = $this
                ->sortBy('category.id')
                ->groupBy(function ($item) {
                    return $item->category ? $item->category->name : 'Inne';
                });

            if (isset($collection['Inne'])) {
                // move category at the end
                $collection->put('Inne', $collection->splice(0, 1)['Inne']);
            }

            return $collection;
        });

        Request::macro('getClientHost', function () {
            if (app()->environment() !== 'production') {
                return '';
            }

            $start = microtime(true);

            if (empty($this->clientHost)) {
                $this->clientHost = gethostbyaddr($this->ip());
            }

            $stop = microtime(true);
            logger()->debug("Host lookup time: " . ($stop - $start) . "ms");

            return $this->clientHost;
        });

        Request::macro('browser', function () {
            return str_limit(Str::ascii($this->header('User-Agent')), 900);
        });

        MorphMany::macro('sync', function (?array $assets) {
            if (!is_array($assets)) {
                return;
            }

            /** @var \Coyote\Post|\Coyote\Pm|\Coyote\Microblog|\Coyote\Firm $parent */
            $parent = $this->getParent();

            $assets = collect($assets)->map(fn($attributes) => Asset::find($attributes['id']))->keyBy('id');

            $ids = $assets->pluck('id')->toArray();
            $current = $parent->assets->keyBy('id');

            $detach = array_diff($current->keys()->toArray(), $ids);
            $attach = array_diff($ids, $current->keys()->toArray());

            foreach ($attach as $id) {
                $parent->assets()->save($assets[$id]);
            }

            foreach ($detach as $id) {
                $current[$id]->delete();
            }
        });
    }
}
