<?php
namespace Features\Dsl\Driver\Channel\IntegrationChannel;

use Coyote\Modules\Campaigns\Eloquent;
use Features\Dsl\Driver\Channel\InMemoryChannel\InMemoryDriver;
use Features\Dsl\Driver\Driver;
use Modules\Campaigns\ForCampaignBanners;
use Modules\Campaigns\ForRotatingBanners;
use Modules\Campaigns\Store\CampaignsStore;
use Test\Modules\Campaigns\Fixture\TestRotatingBanners;

readonly class IntegrationDriver implements Driver {
    private LaravelKernel $laravel;
    private Driver $driver;

    public function __construct() {
        $this->laravel = new LaravelKernel();
        $this->laravel->bootstrap();
        $rotatingBanners = new TestRotatingBanners();
        $this->laravel->app->instance(ForRotatingBanners::class, $rotatingBanners);
        $this->driver = new InMemoryDriver(
            $this->laravel->app->make(CampaignsStore::class),
            $this->laravel->app->make(ForCampaignBanners::class),
            $rotatingBanners);
    }

    public function initialize(string $feature, string $scenario): void {
        // Currently, clearing the database models serves
        // the purpose of functional isolation.
        Eloquent\Campaign::query()->forceDelete();
    }

    public function finalize(): void {
        $this->laravel->disconnectDatabase();
    }

    public function createCampaign(string $campaign, bool $premium): void {
        $this->driver->createCampaign($campaign, $premium);
    }

    public function addVariant(string $campaign, string $variantType, string $variantUrl): void {
        $this->driver->addVariant($campaign, $variantType, $variantUrl);
    }

    public function resolveVariantsForUser(string $deviceType): void {
        $this->driver->resolveVariantsForUser($deviceType);
    }

    public function variantsForSlot(string $slotType): array {
        return $this->driver->variantsForSlot($slotType);
    }

    public function captureDiagnostics(string $testTitle) {}
}
