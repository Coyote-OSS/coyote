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
use Neon\View\JobOffer;
use Neon\View\NeonApplication;

class SessionRepository
{
    public function all(?string $searchPhrase): array
    {
        $jobOfferTitles = $this->jobOfferTitles();
        $filtered = \array_filter($jobOfferTitles, fn(string $title) => str_contains($title, $searchPhrase));
        return \array_map($this->jobOffer(...), $filtered);
    }

    public function add(string $jobOfferTitle): void
    {
        session()->put('jobOffers', \json_encode([...$this->jobOfferTitles(), $jobOfferTitle]));
    }

    private function jobOffer(string $offerTitle): JobOffer
    {
        return new JobOffer(
            $offerTitle,
            '',
            [],
            \Neon\View\WorkMode::Stationary,
            false,
            false,
            '',
            [],
            null,
            null,
            0,
            null,
            null,
            null,
            false,
            \Neon\View\Settlement::Hourly);
    }

    private function jobOfferTitles(): mixed
    {
        return json_decode(session()->get('jobOffers', '[]'), true, \JSON_THROW_ON_ERROR);
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
                $searchPhrase = request()->query->get('search');
                $jobBoard = new Neon\View\NeonApplication();
                foreach ($repo->all($searchPhrase) as $jobOffer) {
                    $jobBoard->addOffer($jobOffer);
                }
                return neonView($jobBoard);
            });
            Route::get('/neon/assets/{filename}', function (string $filename): Response {
                $neon = new Neon\View\NeonApplication();
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

function neonView(NeonApplication $application): string
{
    return <<<html
        <html>
        <head>
            <meta name="viewport" content="width=device-width, initial-scale=1">
            {$application->htmlMarkupHead()}
        </head>
        <body>
        <div id="neon-application">{$application->htmlMarkupBody()}</div>
        </body>
        </html>
        html;
}
