<?php
namespace Coyote\Modules\Campaigns;

use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Modules\Campaigns;
use Modules\Campaigns\ForPriviligedUsers;

class CampaignsServiceProvider extends ServiceProvider {
    public function boot(): void {
        $this->app->instance(ForPriviligedUsers::class, new AuthPriviligedUsers());
        $this->app->singleton(Campaigns\Campaigns::class, function (Application $app) {
            return new Campaigns\Campaigns($app->get(ForPriviligedUsers::class));
        });
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
