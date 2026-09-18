<?php
namespace Features\Dsl\Driver\Channel\IntegrationChannel;

use Coyote\Modules\Campaigns\Eloquent;
use Features\Dsl\Driver\Channel\InMemoryChannel\InMemoryDriver;
use Features\Dsl\Driver\Driver;
use Illuminate\Contracts\Console;
use Illuminate\Database\DatabaseManager;
use Illuminate\Foundation\Application;
use Libs\Arrays\arrays;
use Modules\Campaigns\CampaignBanner;
use Modules\Campaigns\CampaignBannerSet;
use Modules\Campaigns\DeviceType;
use Modules\Campaigns\ForCampaignBanners;
use Modules\Campaigns\ForRotatingBanners;
use Modules\Campaigns\Store\CampaignsStore;
use Test\Modules\Campaigns\Fixture\TestRotatingBanners;

class IntegrationDriver implements Driver {
    private readonly Application $app;
    private readonly Driver $driver;
    private readonly ForCampaignBanners $service;
    private readonly TestRotatingBanners $rotatingBanners;
    private ?CampaignBannerSet $resolvedBanners;

    public function __construct() {
        $this->app = require __DIR__ . '/../../../../../bootstrap/app.php';
        $this->app->make(Console\Kernel::class)->bootstrap();
        Eloquent\Campaign::query()->forceDelete();
        $this->driver = new InMemoryDriver($this->app->make(CampaignsStore::class));
        $this->rotatingBanners = new TestRotatingBanners();
        $this->app->instance(ForRotatingBanners::class, $this->rotatingBanners);
        $this->service = $this->app->make(ForCampaignBanners::class);
    }

    public function createCampaign(string $campaign, bool $premium): void {
        $this->driver->createCampaign($campaign, $premium);
    }

    public function addVariant(string $campaign, string $variantType, string $variantUrl): void {
        $this->driver->addVariant($campaign, $variantType, $variantUrl);
    }

    public function resolveVariantsForUser(string $deviceType): void {
        $this->resolvedBanners = $this->service->bannerSet($this->deviceType($deviceType));
        $this->rotatingBanners->rotate();
    }

    public function variantsForSlot(string $slotType): array {
        return match ($slotType) {
            'square' => $this->resolvedBanners->sidebar === null
                ? []
                : [$this->resolvedBanners->sidebar->imageUrl],
            'feed'   => $this->resolvedBanners->feed |> arrays::map(fn(CampaignBanner $banner) => $banner->imageUrl),
            'header' => $this->resolvedBanners->horizontal |> arrays::map(fn(CampaignBanner $banner) => $banner->imageUrl),
            default  => throw new \Exception()
        };
    }

    public function close(): void {
        $this->database()->disconnect();
    }

    private function database(): DatabaseManager {
        return $this->app->make(DatabaseManager::class);
    }

    private function deviceType(string $deviceType): DeviceType {
        return match ($deviceType) {
            'desktop' => DeviceType::Desktop,
            'mobile'  => DeviceType::Mobile,
            default   => throw new \Exception()
        };
    }
}
