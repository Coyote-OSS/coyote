<?php
require __DIR__ . '/../../../vendor/autoload.php';

use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Neon\View\NeonApplication;

class SessionRepository
{
    public function all(): array
    {
        return json_decode(session()->get('jobOffers', '[]'), true, \JSON_THROW_ON_ERROR);
    }

    public function add(string $jobOfferTitle): void
    {
        session()->put('jobOffers', \json_encode([...$this->all(), $jobOfferTitle]));
    }
}

Application::configure(__DIR__ . DIRECTORY_SEPARATOR . 'laravel')
    ->withRouting(function (): void {
        Route::middleware([
            AddQueuedCookiesToResponse::class,
            StartSession::class,
        ])->group(function () {
            Route::get('/integration/job-offers', function (Request $request, SessionRepository $repo) {
                $repo->add($request->query->get('jobOfferTitle'));
                return \response(status:201);
            });
            Route::get('/job-offers', function (SessionRepository $repo): string {
                $jobBoard = new Neon\View\NeonApplication('/');
                foreach ($repo->all() as $jobOffer) {
                    $jobBoard->addOffer($jobOffer);
                }
                return neonView($jobBoard);
            });
            Route::get('/assets/{filename}', function (string $filename): Response {
                $neon = new Neon\View\NeonApplication('/');
                return response(File::get($neon->assetPath("/assets/$filename")));
            });
        });
    })
    ->booted(fn() => Facades\Config::set('app.debug', true))
    ->booted(function () {
        Facades\Config::set('session', [
            'driver'          => 'cookie',
            'cookie'          => 'neon',
            'path'            => '/',
            'domain'          => '',
            'lifetime'        => 120,
            'lottery'         => [2, 100],
            'expire_on_close' => false,
            'secure'          => false,
        ]);
    })
    ->booted(function () {
        $date = \date('Y-m-d.H-i-s');
        Facades\Config::set('logging', [
            'default'  => 'single',
            'channels' => [
                'single' => [
                    'driver' => 'single',
                    'path'   => "ERROR.$date.log",
                ],
            ],
        ]);
    })
    ->withExceptions()
    ->create()
    ->handleRequest(Request::capture());

function neonView(NeonApplication $neon): string
{
    return <<<html
        <html>
        <head>{$neon->htmlMarkupHead()}</head>
        <body>
        <div id="neon-application">{$neon->htmlMarkupBody()}</div>
        </body>
        </html>
        html;
}
