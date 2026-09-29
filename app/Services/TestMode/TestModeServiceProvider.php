<?php
namespace Coyote\Services\TestMode;

use Illuminate\Support\ServiceProvider;

class TestModeServiceProvider extends ServiceProvider {
    public function register(): void {
        $this->app->bind(TestMode::class, EnvironmentTestMode::class);
    }
}
