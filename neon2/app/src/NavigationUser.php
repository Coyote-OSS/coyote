<?php
namespace Neon2;

readonly class NavigationUser {
    /**
     * @param Notification[] $notifications
     */
    public function __construct(
        public string  $username,
        public string  $profileHref,
        public int     $messagesCount,
        public int     $notificationsCount,
        public array   $notifications,
        public ?string $avatarUrl,
        public ?string $avatarInitials,
        public bool    $canAccessAdministratorPanel,
    ) {}
}
