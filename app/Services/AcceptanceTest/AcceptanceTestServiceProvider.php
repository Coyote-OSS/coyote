<?php
namespace Coyote\Services\AcceptanceTest;

use Illuminate\Support\ServiceProvider;

class AcceptanceTestServiceProvider extends ServiceProvider {
    public function register(): void {
        $this->app->bind(AcceptanceTest::class, EnvironmentAcceptanceTest::class);
    }
}
