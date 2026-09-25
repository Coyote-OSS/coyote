<?php
namespace Tests\Integration\Fixture\Acceptance;

use Coyote\Services\AcceptanceTest\AcceptanceTest;

readonly class ConstantAcceptanceTest implements AcceptanceTest {
    public function __construct(private bool $testMode) {}

    public function isTestMode(): bool {
        return $this->testMode;
    }
}
