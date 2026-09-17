<?php
namespace Features\Dsl\Driver\Channel\IntegrationChannel;

use Coyote\Modules\Campaigns\Eloquent;
use Features\Dsl\Driver\Channel\InMemoryChannel\InMemoryDriver;
use Features\Dsl\Driver\Driver;
use Illuminate\Contracts\Console;
use Illuminate\Database\DatabaseManager;
use Illuminate\Foundation\Application;
use Modules\Campaigns\Store\CampaignsStore;

readonly class IntegrationDriver implements Driver {
    private Application $app;
    private Driver $driver;

    public function __construct() {
        $this->app = require __DIR__ . '/../../../../../bootstrap/app.php';
        $this->app->make(Console\Kernel::class)->bootstrap();
        Eloquent\Campaign::query()->forceDelete();
        $this->driver = new InMemoryDriver($this->app->make(CampaignsStore::class));
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

    public function close(): void {
        $this->database()->disconnect();
    }

    private function database(): DatabaseManager {
        return $this->app->make(DatabaseManager::class);
    }
}
