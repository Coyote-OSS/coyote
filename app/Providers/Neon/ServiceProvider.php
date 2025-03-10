<?php
namespace Coyote\Providers\Neon;

use Coyote\Domain\StringHtml;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Neon;

class ServiceProvider extends RouteServiceProvider
{
    public function loadRoutes(): void
    {
        $this->get('/events', [
            'uses' => fn() => redirect('https://wydarzenia.4programmers.net/'),
        ]);
        $this->middleware(['web', 'geocode'])->group(function () {
            $this->get('/Praca/Modern', function (): View {
                if (!Gate::check('alpha-access')) {
                    abort(404);
                }
                $neon = new Neon\View\NeonApplication('/neon');
                $repository = app(JobElasticSearchRepository::class);
                foreach ($repository->jobOffers() as $jobOffer) {
                    $neon->addOffer($jobOffer->title);
                }
                return view('job.home_modern', [
                    'head'     => new StringHtml($neon->htmlMarkupHead()),
                    'jobBoard' => new StringHtml($neon->htmlMarkupBody()),
                ]);
            });
        });
    }
}
