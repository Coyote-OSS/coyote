<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the "environment" your application is currently
    | running in. This may determine how you prefer to configure various
    | services your application utilizes. Set this in your ".env" file.
    |
    */

    'env'     => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Application Version
    |--------------------------------------------------------------------------
    |
    */
    'version' => '2.5',

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    */

    'debug' => env('APP_DEBUG'),
    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | your application so that it is used when running Artisan tasks.
    |
    */

    'url'       => env('APP_URL', docker_secret('APP_URL_FILE')),
    'asset_url' => '/',

    /*
    |--------------------------------------------------------------------------
    | Application name
    |--------------------------------------------------------------------------
    */
    'name'      => '4programmers.net',

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. We have gone
    | ahead and set this to a sensible default for you out of the box.
    |
    */

    'timezone' => 'Europe/Warsaw',
    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by the translation service provider. You are free to set this value
    | to any of the locales which will be supported by the application.
    |
    */

    'locale' => 'pl',
    /*
    |--------------------------------------------------------------------------
    | Application Fallback Locale
    |--------------------------------------------------------------------------
    |
    | The fallback locale determines the locale to use when the current one
    | is not available. You may change the value to correspond to any of
    | the language folders that are provided through your application.
    |
    */

    'fallback_locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Faker Locale
    |--------------------------------------------------------------------------
    |
    | This locale will be used by the Faker PHP library when generating fake
    | data for your database seeds. For example, this will be used to get
    | localized telephone numbers, street address information and more.
    |
    */
    'faker_locale'    => 'pl_PL',

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is used by the Illuminate encrypter service and should be set
    | to a random, 32 character string, otherwise these encrypted strings
    | will not be safe. Please do this before deploying an application!
    |
    */

    'key'    => env('APP_KEY', docker_secret('APP_KEY_FILE')),
    'cipher' => 'AES-256-CBC',

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log settings for your application. Out of
    | the box, Laravel uses the Monolog PHP logging library. This gives
    | you a variety of powerful log handlers / formatters to utilize.
    |
    | Available Settings: "single", "daily", "syslog", "errorlog"
    |
    */

    'log' => 'daily',
    /*
    |--------------------------------------------------------------------------
    | Autoloaded Service Providers
    |--------------------------------------------------------------------------
    |
    | The service providers listed here will be automatically loaded on the
    | request to your application. Feel free to add your own services to
    | this array to grant expanded functionality to your applications.
    |
    */

    'providers' => [

        /*
         * Laravel Framework Service Providers...
         */
        Illuminate\Auth\AuthServiceProvider::class,
        Illuminate\Broadcasting\BroadcastServiceProvider::class,
        Illuminate\Bus\BusServiceProvider::class,
        Illuminate\Cache\CacheServiceProvider::class,
        Illuminate\Foundation\Providers\ConsoleSupportServiceProvider::class,
        Illuminate\Cookie\CookieServiceProvider::class,
        Illuminate\Database\DatabaseServiceProvider::class,
        Illuminate\Encryption\EncryptionServiceProvider::class,
        Illuminate\Filesystem\FilesystemServiceProvider::class,
        Illuminate\Foundation\Providers\FoundationServiceProvider::class,
        Illuminate\Hashing\HashServiceProvider::class,
        Illuminate\Mail\MailServiceProvider::class,
        Illuminate\Notifications\NotificationServiceProvider::class,
        Illuminate\Pagination\PaginationServiceProvider::class,
        Illuminate\Pipeline\PipelineServiceProvider::class,
        Illuminate\Queue\QueueServiceProvider::class,
        Illuminate\Redis\RedisServiceProvider::class,
        Illuminate\Auth\Passwords\PasswordResetServiceProvider::class,
        Illuminate\Session\SessionServiceProvider::class,
        Illuminate\Translation\TranslationServiceProvider::class,
        Illuminate\Validation\ValidationServiceProvider::class,
        Illuminate\View\ViewServiceProvider::class,

        // Obsluga szablonow Twig. Ten serwis umieszczony jest tutaj poniewaz chcemy
        // aby twig umozliwial generowanie stron bledow na niskim poziomie (np. przy braku polaczenia
        // z baza danych)
        TwigBridge\ServiceProvider::class,

        // Profiler aplikacji (to musi byc w tym miejscu)
        Barryvdh\Debugbar\ServiceProvider::class,
        Coyote\Providers\ImageServiceProvider::class,

        /*
         * Application Service Providers...
         */
        Coyote\Providers\RepositoryServiceProvider::class,
        Coyote\Providers\AppServiceProvider::class,
        Coyote\Providers\ConfigServiceProvider::class,

        // Obsluga sesji przez Coyote (nadpisujemy domyslny driver)
        Coyote\Providers\SessionServiceProvider::class,
        Coyote\Providers\EventServiceProvider::class,
        Coyote\Providers\Neon\ServiceProvider::class,
        Neon2\Laravel\RouteServiceProvider::class,
        Coyote\Providers\RouteServiceProvider::class,
        Coyote\Providers\ViewServiceProvider::class,
        Coyote\Providers\AuthServiceProvider::class,
        Coyote\Providers\BroadcastServiceProvider::class,
        // mozliwosc wczytania gotowych "scenariuszy" parsowania elementow strony, takich jak
        // mikroblogi, czy forum. w takim scenariuszu zaladowane sa odpowiednie klasy
        // do parsowania tekstu w zaleznosci od tego, czy mamy do czynienia z postem, komentarzem itd
        Coyote\Providers\ParserServiceProvider::class,
        // Klasy obslugi reputacji uzytkownika
        Coyote\Providers\ReputationServiceProvider::class,
        // Obsluga Elasticsearch
        Coyote\Providers\ElasticsearchServiceProvider::class,
        // Geokodowanie przy pomocy uslugi geo-ip.pl
        Coyote\Providers\GeoIpServiceProvider::class,
        // Google maps geocoder
        Coyote\Providers\GeocoderServiceProvider::class,
        // Uploadowanie zalacznikow, zdjec oraz generowanie URL do tychze plikow
        Coyote\Providers\MediaServiceProvider::class,
        // Generowanie mapy strony
        Coyote\Providers\SitemapProvider::class,

        // Klasa wspierajaca budowanie znacznikow HTML
        Collective\Html\HtmlServiceProvider::class,

        // Mozliwosc logowania przez github, fb, google...
        Laravel\Socialite\SocialiteServiceProvider::class,

        // Pakiet do budowania menu
        Lavary\Menu\ServiceProvider::class,
        // Parsowanie user agent
        Jenssegers\Agent\AgentServiceProvider::class,
        // Pakiet Laravel Grid do budowania tabel oraz filtrowania i sortowania
        Boduch\Grid\GridServiceProvider::class,
        // kursy wymiany walut
        Swap\Laravel\SwapServiceProvider::class,
        Coyote\Providers\SeoServiceProvider::class,
        Coyote\Feature\JobBoard\JobBoardServiceProvider::class,
    ],
    /*
    |--------------------------------------------------------------------------
    | Class Aliases
    |--------------------------------------------------------------------------
    |
    | This array of class aliases will be registered when this application
    | is started. However, feel free to register as many as you wish as
    | the aliases are "lazy" loaded so they don't hinder performance.
    |
    */

    'aliases' => [
        'Auth'     => Illuminate\Support\Facades\Auth::class,
        'Blade'    => Illuminate\Support\Facades\Blade::class,
        'DB'       => Illuminate\Support\Facades\DB::class,
        'Mail'     => Illuminate\Support\Facades\Mail::class,
        'Password' => Illuminate\Support\Facades\Password::class,
        'Request'  => Illuminate\Support\Facades\Request::class,
        'Schema'   => Illuminate\Support\Facades\Schema::class,
        'Session'  => Illuminate\Support\Facades\Session::class,
        'URL'      => Illuminate\Support\Facades\URL::class,
        'View'     => Illuminate\Support\Facades\View::class,
    ],
];
