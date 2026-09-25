<?php
namespace Coyote\Services\AcceptanceTest;

interface AcceptanceTest {
    public function isTestMode(): bool;
}
