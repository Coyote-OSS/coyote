<?php
namespace Coyote\Providers\Neon;

use Coyote\Domain\StringHtml;
use Coyote\Job\Location;
use Coyote\Services\UrlBuilder;
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
                $neon = new Neon\NeonApplication('/neon');
                $repository = app(JobElasticSearchRepository::class);
                foreach ($repository->jobOffers() as $jobOffer) {
                    $neon->addJobOffer(
                        $jobOffer->title,
                        UrlBuilder::job($jobOffer, true),
                        $jobOffer->boost_at->format('Y-m-d'),
                        $jobOffer->salary_to,
                        $jobOffer->salary_from,
                        $jobOffer->is_remote ? 'remote' : 'stationary',
                        $jobOffer->locations
                            ->map(fn(Location $location): string => $location->city)
                            ->filter(fn(string $city) => !empty($city))
                            ->toArray(),
                        $jobOffer->firm->name);
                }
                return view('job.home_modern', [
                    'neonHead' => new StringHtml($neon->htmlMarkupHead()),
                    'neonBody' => new StringHtml($neon->htmlMarkupBody()),
                ]);
            });
        });
    }
}
