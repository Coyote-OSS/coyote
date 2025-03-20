<?php
namespace Coyote\Providers\Neon;

use Coyote;
use Coyote\Domain\Settings\UserTheme;
use Coyote\Domain\StringHtml;
use Coyote\Job;
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
            $this->get('/Praca/Modern', function (UserTheme $theme): View {
                if (!Gate::check('alpha-access')) {
                    abort(404);
                }
                $neon = new Neon\NeonApplication('/neon');
                $repository = app(JobElasticSearchRepository::class);
                foreach ($repository->jobOffers() as $jobOffer) {
                    $neon->addJobOffer($this->jobOffer($jobOffer));
                }
                return view('job.home_modern', [
                    'neonHead' => new StringHtml($neon->htmlMarkupHead()),
                    'neonBody' => new StringHtml($neon->htmlMarkupBody($theme->isThemeDark() ? Neon\Theme::Dark : Neon\Theme::Light)),
                ]);
            });
        });
    }

    private function jobOffer(Coyote\Job $jobOffer): Neon\JobOffer
    {
        return new Neon\JobOffer(
            $jobOffer->title,
            UrlBuilder::job($jobOffer, true),
            $jobOffer->boost_at->format('Y-m-d'),
            $this->jobOfferSalary($jobOffer),
            $this->workMode($jobOffer),
            $jobOffer->locations
                ->map(fn(Location $location): string => $location->city)
                ->filter(fn(string $city) => !empty($city))
                ->toArray(),
            $jobOffer->firm->name,
            $this->jobOfferLogoUrl($jobOffer),
            $this->jobOfferTags($jobOffer),
            $this->jobOfferLegalForm($jobOffer),
            $this->isSubscribed($jobOffer),
            $this->isMine($jobOffer),
            $this->isPromoted($jobOffer));
    }

    private function workMode(Job $jobOffer): Neon\WorkMode
    {
        if (!$jobOffer->is_remote) {
            return Neon\WorkMode::Stationary;
        }
        if ($jobOffer->remote_range === 100) {
            return Neon\WorkMode::FullyRemote;
        }
        return Neon\WorkMode::Hybrid;
    }

    /**
     * @return string[]
     */
    private function jobOfferTags(Coyote\Job $jobOffer): array
    {
        return $jobOffer->tags
            ->map(fn(Coyote\Tag $tag) => $tag->real_name ?? $tag->name)
            ->toArray();
    }

    private function jobOfferLegalForm(Coyote\Job $jobOffer): Neon\LegalForm
    {
        return match ($jobOffer->employment) {
            'employment' => Neon\LegalForm::EmploymentContract,
            'mandatory' => Neon\LegalForm::ContractOfMandate,
            'contract' => Neon\LegalForm::ContractForSpecificTask,
            'b2b' => Neon\LegalForm::BusinessToBusiness,
        };
    }

    private function jobOfferLogoUrl(Job $jobOffer): ?string
    {
        if ($jobOffer->firm->logo->getFilename() === null) {
            return null;
        }
        return (string)($jobOffer->firm->logo->url());
    }

    private function jobOfferSalary(Job $jobOffer): ?Neon\Salary
    {
        if ($jobOffer->salary_from === null && $jobOffer->salary_to === null) {
            return null;
        }
        return new Neon\Salary(
            $jobOffer->salary_from ?? $jobOffer->salary_to,
            $jobOffer->salary_to ?? $jobOffer->salary_from,
            Neon\Currency::from($jobOffer->currency->name),
            Neon\Rate::from($jobOffer->rate),
            !$jobOffer->is_gross);
    }

    private function isSubscribed(Job $jobOffer): bool
    {
        if (auth()->check()) {
            return $jobOffer->subscribers()->forUser(auth()->user()->id)->exists();
        }
        return false;
    }

    private function isMine(Job $jobOffer): bool
    {
        if (auth()->check()) {
            return $jobOffer->user_id === auth()->user()->id;
        }
        return false;
    }

    private function isPromoted(Job $jobOffer): bool
    {
        return $jobOffer->is_ads;
    }
}
