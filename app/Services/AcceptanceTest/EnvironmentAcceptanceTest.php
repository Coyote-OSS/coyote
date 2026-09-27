<?php
namespace Coyote\Services\AcceptanceTest;

use Illuminate\Support\Env;

readonly class EnvironmentAcceptanceTest implements AcceptanceTest {
    public function isAcceptanceTest(): bool {
        return Env::get('ACCEPTANCE_TEST') === 'acceptance';
    }
}
