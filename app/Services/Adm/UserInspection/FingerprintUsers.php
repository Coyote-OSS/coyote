<?php
namespace Coyote\Services\Adm\UserInspection;

readonly class FingerprintUsers {
    /**
     * @param int[] $userIds
     */
    public function __construct(
        public string $fingerprint,
        public array  $userIds,
    ) {}
}
