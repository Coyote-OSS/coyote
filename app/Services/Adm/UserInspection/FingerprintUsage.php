<?php
namespace Coyote\Services\Adm\UserInspection;

readonly class FingerprintUsage {
    public function __construct(
        public string $fingerprint,
        public int    $timesUsed,
        public string $lastUsed,
    ) {}
}
