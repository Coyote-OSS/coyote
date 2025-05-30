<?php
namespace Coyote\Providers\Neon;

use Coyote;
use Coyote\Domain\Settings\UserTheme;
use Coyote\Domain\StringHtml;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider;
use Neon2\AcceptanceTest;
use Neon2\Coyote\Integration;
use Neon2\Invoice\CountryGate;
use Neon2\JobBoardView;
use Neon2\Payment\Provider\PaymentProvider;
use Neon2\Payment\Provider\PaymentWebhook;
use Neon2\Payment\Provider\Stripe;
use Neon2\Payment\Provider\StripeWebhook;
use Neon2\Payment\Provider\TestPaymentProvider;
use Neon2\Payment\Provider\TestPaymentWebhook;
use Neon2\StripePublicKey;
use Neon2\StripeSecrets;

class ServiceProvider extends RouteServiceProvider {
    public function register(): void {
        parent::register();
        $this->app->bind(CountryGate::class, EloquentCountryGate::class);
        $this->app->bind(Integration::class, CoyoteIntegration::class);
        if (new AcceptanceTest()->isTestMode()) {
            $this->app->bind(PaymentProvider::class, TestPaymentProvider::class);
            $this->app->bind(PaymentWebhook::class, TestPaymentWebhook::class);
            $this->app->instance(StripePublicKey::class, new StripePublicKey(null));
        } else {
            $secrets = new StripeSecrets(
                config('services.stripe.key'),
                config('services.stripe.secret'),
                config('services.stripe.endpoint_secret'));
            $this->app->instance(PaymentProvider::class, new Stripe($secrets->secretKey));
            $this->app->instance(PaymentWebhook::class, new StripeWebhook($secrets->webhookSigningSecret));
            $this->app->instance(StripePublicKey::class, new StripePublicKey($secrets->publishableKey));
        }
    }

    public function loadRoutes(): void {
        $this->get('/events', [
            'uses' => fn() => redirect('https://wydarzenia.4programmers.net/'),
        ]);
        $this->middleware(['web', 'geocode'])->group(function () {
            $this
                ->name('neon.jobOffer.pricing')
                ->get('/Job/pricing', $this->indexView(...));
            $this
                ->get('/Job/{id}/edit', $this->indexView(...));
            $this
                ->name('neon.jobOffer.show')
                ->get('/Job/{slug}/{id}', $this->indexView(...));
            $this
                ->name('neon.jobOffer.list')
                ->get('/Job/{fallback?}', $this->indexView(...))
                ->where('fallback', '.*');
        });
    }

    private function indexView(
        Integration     $integration,
        CountryGate     $countries,
        StripePublicKey $stripePublicKey,
        UserTheme       $theme,
        AcceptanceTest  $test,
    ): string {
        if ($test->isTestMode()) {
            if (request()->query->has('workerIndex')) {
                $userId = $this->acceptanceTestUserId(request()->query->get('workerIndex'));
                auth()->loginUsingId($userId, remember:true);
            }
            if (\request()->query->has('revokeBundle')) {
                $integration->revokePlanBundle(auth()->id());
            }
            if (request()->query->has('acceptanceTagNames')) {
                $acceptanceTagNames = json_decode(request()->query->get('acceptanceTagNames'));
            } else {
                $acceptanceTagNames = [];
            }
        } else {
            $acceptanceTagNames = [];
        }
        $jobBoardView = new JobBoardView(
            $integration,
            $countries,
            $stripePublicKey->publishableKey,
            config('services.google-maps.key'),
            $test->isTestMode(),
            auth()->id(),
            auth()->user()?->email,
            app('session')->token(),
            $theme->isThemeDark(),
            $theme->themeMode(),
            request()->route()->parameter('id'),
            $acceptanceTagNames);
        return view('job.home_modern', [
            'neonHead' => new StringHtml($jobBoardView->htmlMarkupHead()),
            'neonBody' => new StringHtml($jobBoardView->htmlMarkupBody()),
        ]);
    }

    private function acceptanceTestUserId(int $workerIndex): int {
        $userId = $workerIndex + 1;
        return Coyote\User::query()->where('name', "acceptance-test-$userId")->first()->id;
    }
}
