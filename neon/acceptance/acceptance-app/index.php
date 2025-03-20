<?php
require __DIR__ . '/../../../vendor/autoload.php';

use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades;
use Illuminate\Support\Facades\Route;
use Neon\Currency;
use Neon\JobOffer;
use Neon\NeonApplication;
use Neon\WorkMode;

Application::configure(__DIR__ . DIRECTORY_SEPARATOR . 'laravel')
    ->withRouting(function (): void {
        Route::middleware([
            AddQueuedCookiesToResponse::class,
            StartSession::class,
        ])->group(function () {
            Route::get('/integration/job-offers', function (Request $request) {
                sessionAddJobOffer(new JobOffer(
                    $request->get('jobOfferTitle'),
                    '',
                    $request->get('jobOfferPublishDate'),
                    salary($request->get('jobOfferSalaryTo')),
                    WorkMode::from($request->get('jobOfferWorkMode')),
                    $request->get('jobOfferLocations', []),
                    null,
                    null,
                    $request->get('jobOfferTags', []),
                    \Neon\LegalForm::EmploymentContract,
                    false,
                    false));
                return \response(status:201);
            });
            Route::get('/', function (): Response {
                $neon = neonApplication();
                return response(<<<EOF
                    <html>
                    <head>
                        <meta name="viewport" content="width=device-width,initial-scale=1">
                        {$neon->htmlMarkupHead()}
                    </head>
                    <body class="bg-tile-nested">{$neon->htmlMarkupBody()}</body>
                    </html>
                    EOF,
                );
            });
            Route::get('/neon/assets/{filename}', function (string $filename): Response {
                $neon = new NeonApplication('/neon');
                return response($neon->viteFile("/assets/$filename"));
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

function neonApplication(): NeonApplication
{
    $neon = new NeonApplication('/neon');
    $neon->addJobOffer(new JobOffer('Swift Developer', '',
        '2023-03-03',
        salaryRange(2000, 3000, \Neon\Rate::Weekly),
        WorkMode::FullyRemote,
        ['New York'],
        'Spotify',
        null,
        ['swift', 'ios'],
        \Neon\LegalForm::EmploymentContract,
        false,
        true));
    $neon->addJobOffer(new JobOffer('Rust Developer', '',
        '2000-01-01',
        salaryRange(75, 100, \Neon\Rate::Hourly),
        WorkMode::Stationary,
        ['London'],
        'Facebook',
        null,
        ['rust', 'cargo'],
        \Neon\LegalForm::BusinessToBusiness,
        false,
        false));
    $neon->addJobOffer(new JobOffer('Go Developer', '',
        '2012-02-02',
        salaryRange(20000, 22500),
        WorkMode::Hybrid,
        ['Amsterdam'],
        'Microsoft',
        null,
        ['go', 'golang'],
        \Neon\LegalForm::ContractForSpecificTask,
        false,
        true));
    foreach (sessionJobOffers() as $jobOffer) {
        $neon->addJobOffer($jobOffer);
    }
    return $neon;
}

/**
 * @return JobOffer[]
 */
function sessionJobOffers(): array
{
    return \unserialize(session()->get('values', serialize([])));
}

function sessionAddJobOffer(JobOffer $jobOffer): void
{
    session()->put('values', \serialize([...sessionJobOffers(), $jobOffer]));
}

function salary(?int $salaryTo): ?\Neon\Salary
{
    if ($salaryTo === null) {
        return null;
    }
    return salaryRange($salaryTo, $salaryTo);
}

function salaryRange(int $salaryFrom, int $salaryTo, ?\Neon\Rate $rate = null): \Neon\Salary
{
    return new \Neon\Salary(
        $salaryFrom,
        $salaryTo,
        Currency::PLN,
        $rate ?? \Neon\Rate::Monthly,
        true);
}
