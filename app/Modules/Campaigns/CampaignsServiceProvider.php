<?php
namespace Coyote\Modules\Campaigns;

use Illuminate\Foundation\Application;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Modules\Campaigns;
use Modules\Campaigns\ForPriviligedUsers;
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

        $this->app->singleton(
            Campaigns\Campaigns::class,
            fn(Application $app) => new Campaigns\Campaigns(
                $app->get(ForPriviligedUsers::class),
                $app->get(Campaigns\ForRotatingBanners::class),));

        $this->registerCampaigns($this->app->get(Campaigns\Campaigns::class));
        $this->registerRoutes($this->app->make(Router::class));
    }

    private function registerCampaigns(Campaigns\Campaigns $campaigns): void {
        $campaigns->add(
            '/img/jobAd/mobileViking/narrow-h250.png',
            '/img/jobAd/mobileViking/wide-h200.png',
            'mobileViking',
            'https://mobilevikings.pl/pl/oferta-na-karte/?utm_source=4programmers&utm_medium=cpc&utm_campaign=voice_display_cpm_null_1');
        $campaigns->add(
            '/img/jobAd/myDevil/narrow-h250.png',
            '/img/jobAd/myDevil/wide-h200.png',
            'myDevil',
            'https://www.mydevil.net/hosting-dla-developerow/?__s=4programmers.net');
    }

    private function registerRoutes(Router $router): void {
        $router->get('/campaigns/{campaignKey}', [CampaignController::class, 'click']);
    }
}
