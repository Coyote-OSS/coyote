<?php
namespace Neon2;

readonly class NavigationUser {
    public function __construct(
        public string  $username,
        public string  $profileHref,
        public int     $messagesCount,
        public int     $notificationsCount,
        public ?string $avatarUrl,
        public ?string $avatarInitials,
        public bool    $canAccessAdministratorPanel,
    ) {}
}
