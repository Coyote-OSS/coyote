<?php
namespace Coyote\Http;

use Coyote\Http\Middleware\ForceRootUrl;
use Coyote\Http\Middleware\ThrottleSubmission;
use Illuminate\Auth\Middleware\AuthenticateWithBasicAuth;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Auth\Middleware\RequirePassword;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Foundation;
use Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use Illuminate\Foundation\Http\Middleware\ValidatePostSize;
use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Http\Middleware\SetCacheHeaders;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Routing\Middleware\ValidateSignature;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class Kernel extends Foundation\Http\Kernel
{
    /** @var array */
    protected $middleware = [
        Middleware\TrustProxies::class,
        HandleCors::class,
        CheckForMaintenanceMode::class,
        ValidatePostSize::class,
        Middleware\TrimStrings::class,
        ConvertEmptyStringsToNull::class,
    ];

    /** @var array */
    protected $middlewareGroups = [
        'web'  => [
            Middleware\EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            Middleware\SetupGuestCookie::class,
            ShareErrorsFromSession::class,
            Middleware\PreventRequestForgery::class,
            SubstituteBindings::class,
            Middleware\DefaultBindings::class,
            Middleware\FirewallBlacklist::class,
            Middleware\SurveilRoutes::class,
            Middleware\RedirectToCanonicalUrl::class,
        ],
        'api'  => [
            'throttle:60,1',
            SubstituteBindings::class,
            Middleware\DefaultBindings::class,
            ForceRootUrl::class,
            Middleware\SurveilRoutes::class,
        ],
    ];

    /** @var array */
    protected $middlewareAliases = [
        'auth'                => Middleware\Authenticate::class,
        'auth.basic'          => AuthenticateWithBasicAuth::class,
        'bindings'            => SubstituteBindings::class,
        'throttle'            => ThrottleRequests::class,
        'throttle.submission' => ThrottleSubmission::class,
        'can'                 => Authorize::class,
        'guest'               => Middleware\RedirectIfAuthenticated::class,
        'cache.headers'       => SetCacheHeaders::class,
        'password.confirm'    => RequirePassword::class,
        'signed'              => ValidateSignature::class,
        'verified'            => EnsureEmailIsVerified::class,
        'adm'                 => Middleware\AdmAccess::class,
        'forum.write'         => Middleware\Forum\ForumWrite::class,
        'forum.url'           => Middleware\RedirectIfUrl::class,
        'topic.access'        => Middleware\Forum\RedirectIfMoved::class,
        'topic.scroll'        => Middleware\RedirectToPost::class,
        'wiki.access'         => Middleware\Forum\WikiAccess::class,
        'wiki.lock'           => Middleware\Forum\WikiLock::class,
        'wiki.legacy'         => Middleware\Forum\WikiLegacy::class,
        'page.hit'            => Middleware\PageHit::class,
        'geocode'             => Middleware\GeocodeIp::class,
        'json'                => Middleware\JsonResponse::class,
    ];

    /** @var array */
    protected $middlewarePriority = [
        StartSession::class,
        ShareErrorsFromSession::class,
        Middleware\Authenticate::class,
        ThrottleRequests::class,
        AuthenticateSession::class,
        SubstituteBindings::class,
        Authorize::class,
    ];
}
