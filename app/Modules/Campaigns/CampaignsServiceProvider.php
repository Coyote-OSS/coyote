<?php
namespace Coyote\Modules\Campaigns;

use Illuminate\Foundation\Application;
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
    }

    private function registerCampaigns(Campaigns\Campaigns $campaigns): void {
        $campaigns->add(
            '/img/jobAd/mobileViking/narrow-h250.png',
            '/img/jobAd/mobileViking/wide-h200.png');
        $campaigns->add(
            '/img/jobAd/myDevil/narrow-h250.png',
            '/img/jobAd/myDevil/wide-h200.png');
    }
}
