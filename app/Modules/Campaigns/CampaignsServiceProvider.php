<?php
namespace Coyote\Modules\Campaigns;

use Coyote\Modules\Campaigns\Eloquent\EloquentCampaignsStore;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Modules\Campaigns;
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

        $this->registerRoutes($this->app->make(Router::class));
    }

    private function registerRoutes(Router $router): void {
        $router->get('/campaigns/{variantId}', [CampaignsController::class, 'click']);
    }
}
