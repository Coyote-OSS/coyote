<?php
namespace Coyote\Services\Adm\UserInspection;

readonly class Fingerprint {
    public string $fingerprintHref;

    public function __construct(
        public string $fingerprint,
        public int    $timesUsed,
        public string $lastUsed,
    ) {
        $this->fingerprintHref = $this->fingerprintHref($this->fingerprint);
    }

    private function fingerprintHref(string $fingerprint): string {
        return route('adm.stream') . '?' . http_build_query(['fingerprint' => $fingerprint]);
    }
}
