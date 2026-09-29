<?php
namespace Coyote\Services\TestMode;

use Illuminate\Support\Env;

readonly class EnvironmentTestMode implements TestMode {
    public function isAcceptanceTest(): bool {
        return Env::get('ACCEPTANCE_TEST') === 'acceptance';
    }
}
