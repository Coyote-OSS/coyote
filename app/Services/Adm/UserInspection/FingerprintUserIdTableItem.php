<?php
namespace Coyote\Services\Adm\UserInspection;

readonly class FingerprintUserIdTableItem {
    /**
     * @param int[] $userIds
     */
    public function __construct(
        public string $fingerprint,
        public array  $userIds,
    ) {}
}
