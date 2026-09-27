<?php
namespace Features\Dsl\Driver\Channel\IntegrationChannel;

use Coyote;
use Coyote\Modules\Campaigns\Eloquent;
use Features\Dsl\Driver\Channel\InMemoryChannel\InMemoryDriver;
use Modules\Campaigns\ForCampaignBanners;
use Modules\Campaigns\ForRotatingBanners;
use Modules\Campaigns\Store\CampaignsStore;
use Modules\JobBoard\JobBoardStore;
use Test\Modules\Campaigns\Fixture\TestRotatingBanners;

class IntegrationDriver extends InMemoryDriver {
    private readonly LaravelKernel $laravel;

    public function __construct() {
        $this->laravel = new LaravelKernel();
        $this->laravel->bootstrap();
        $rotatingBanners = new TestRotatingBanners();
        $this->laravel->app->instance(ForRotatingBanners::class, $rotatingBanners);
        parent::__construct(
            $this->laravel->app->make(CampaignsStore::class),
            $this->laravel->app->make(JobBoardStore::class),
            $this->laravel->app->make(ForCampaignBanners::class),
            $rotatingBanners);
    }

    public function initialize(string $feature, string $scenario): void {
        $this->initializeCampaigns();
        $this->initializeJobBoard();
    }

    private function initializeCampaigns(): void {
        // Currently, clearing the database models serves
        // the purpose of functional isolation.
        Eloquent\Campaign::query()->forceDelete();
    }

    private function initializeJobBoard(): void {
        Coyote\Plan::query()
            ->where('name', 'Free')
            ->firstOr(fn() => Coyote\Plan::query()->forceCreate(['name' => 'Free']));
    }

    public function finalize(): void {
        $this->laravel->disconnectDatabase();
    }
}
