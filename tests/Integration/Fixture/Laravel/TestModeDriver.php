<?php
namespace Tests\Integration\Fixture\Laravel;

use Coyote\Services\TestMode\TestMode;
use Tests\Integration\Fixture\Acceptance\ConstantTestMode;
use Tests\Legacy\Integration\BaseFixture\Server\Laravel;

readonly class TestModeDriver {
    public function __construct(
        private Laravel\TestCase $laravel,
        bool                     $testMode,
    ) {
        $laravel->app->instance(TestMode::class, new ConstantTestMode($testMode));
    }

    public function setProductionMode(): void {
        $this->laravel->app->instance(TestMode::class, new ConstantTestMode(false));
    }
}
