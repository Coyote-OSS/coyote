<?php
namespace Coyote\Services\Adm;

readonly class UserInspectionFingerprint {
    public function __construct(
        public string $fingerprint,
        public int    $timesUsed,
        public string $lastUsed,
    ) {}
}
