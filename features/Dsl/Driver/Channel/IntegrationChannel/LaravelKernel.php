<?php
namespace Features\Dsl\Driver\Channel\IntegrationChannel;

use Illuminate\Contracts\Console;
use Illuminate\Database\DatabaseManager;
use Illuminate\Foundation;

readonly class LaravelKernel {
    public Foundation\Application $app;

    public function __construct() {
        $this->app = require __DIR__ . '/../../../../../bootstrap/app.php';
    }

    public function bootstrap(): void {
        $this->app->make(Console\Kernel::class)->bootstrap();
    }

    public function disconnectDatabase(): void {
        $this->database()->disconnect();
    }

    private function database(): DatabaseManager {
        return $this->app->make(DatabaseManager::class);
    }
}
