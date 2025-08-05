<?php
namespace Coyote\Services\Adm\UserInspection;

readonly class Fingerprint {
    public function __construct(
        public string $fingerprint,
        public int    $timesUsed,
        public string $lastUsed,
    ) {}
}
