<?php
namespace Coyote\Providers\Neon;

use Coyote\Domain\StringHtml;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
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
            $this->get('/Praca/Modern', function (JobElasticSearchRepository $repository, Request $request): View {
                if (!Gate::check('alpha-access')) {
                    abort(404);
                }
                $jobBoard = new Neon\View\JobBoard();
                $searchPhrase = $request->query->get('search');
                foreach ($repository->jobOffers($searchPhrase) as $jobOffer) {
                    $jobBoard->addOffer($jobOffer);
                }
                return view('job.home_modern', [
                    'head'     => new StringHtml($jobBoard->htmlMarkupHead()),
                    'jobBoard' => new StringHtml($jobBoard->htmlMarkupBody()),
                ]);
            });
        });
    }
}
