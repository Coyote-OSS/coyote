<?php
namespace Coyote\Services\TestMode;

interface TestMode {
    public function isAcceptanceTest(): bool;
}
