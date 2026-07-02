<?php
namespace Coyote\Modules\Campaigns;

use Coyote\Modules\Campaigns\Eloquent\EloquentCampaignsStore;
use Coyote\Modules\Campaigns\Provided\AuthPriviligedUsers;
use Coyote\Modules\Campaigns\Provided\CarbonCurrentDate;
use Coyote\Modules\Campaigns\Provided\RouteRedirectUrls;
use Coyote\Modules\Campaigns\Provided\TimeRotatingBanners;
use Coyote\Modules\Campaigns\User\Http\CampaignsController;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Modules\Campaigns;
use Modules\Campaigns\CampaignBannersPresenter;
use Modules\Campaigns\ForPresentingBanners;
use Modules\Campaigns\ForRedirectUrls;
use Modules\Campaigns\Store\CampaignsStore;
use Psr\Clock\ClockInterface;
use Symfony;

class CampaignsServiceProvider extends ServiceProvider {
    public function boot(): void {
        $this->app->instance(
            Campaigns\ForPriviligedUsers::class,
            new AuthPriviligedUsers());

        $this->app->bind(
            ClockInterface::class,
            fn() => new Symfony\Component\Clock\Clock());

        $this->app->bind(
            Campaigns\ForRotatingBanners::class,
            TimeRotatingBanners::class);

        $this->app->bind(
            CampaignsStore::class,
            EloquentCampaignsStore::class);

        $this->app->bind(
            Campaigns\ForCurrentDate::class,
            CarbonCurrentDate::class);

        $this->app->bind(
            ForRedirectUrls::class,
            RouteRedirectUrls::class);

        $this->app->bind(
            ForPresentingBanners::class,
            CampaignBannersPresenter::class);

        $this->registerRoutes($this->app->make(Router::class));
    }

    private function registerRoutes(Router $router): void {
        $router
            ->get('/campaigns/{variantId}', [CampaignsController::class, 'click'])
            ->name('campaigns.click');
        $router
            ->post('/campaigns/{variantId}/expose', [CampaignsController::class, 'expose'])
            ->name('campaigns.expose');
    }
}
