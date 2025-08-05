<?php
namespace Coyote\Services\Adm\UserInspection;

readonly class User {
    public function __construct(
        public int    $userId,
        public string $username,
    ) {}
}
