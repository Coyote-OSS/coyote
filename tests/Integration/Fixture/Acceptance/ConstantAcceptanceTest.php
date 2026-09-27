<?php
namespace Tests\Integration\Fixture\Acceptance;

use Coyote\Services\AcceptanceTest\AcceptanceTest;

readonly class ConstantAcceptanceTest implements AcceptanceTest {
    public function __construct(private bool $acceptanceTest) {}

    public function isAcceptanceTest(): bool {
        return $this->acceptanceTest;
    }
}
