<?php
namespace Coyote\Services\Adm\UserInspection;

readonly class FingerprintUserTableItem {
    /**
     * @param User[] $users
     */
    public function __construct(
        public string $fingerprint,
        public array  $users,
    ) {}
}
