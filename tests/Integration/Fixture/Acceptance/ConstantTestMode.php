<?php
namespace Tests\Integration\Fixture\Acceptance;

use Coyote\Services\TestMode\TestMode;

readonly class ConstantTestMode implements TestMode {
    public function __construct(private bool $acceptanceTest) {}

    public function isAcceptanceTest(): bool {
        return $this->acceptanceTest;
    }
}
