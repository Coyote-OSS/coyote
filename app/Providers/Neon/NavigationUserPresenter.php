<?php
namespace Coyote\Providers\Neon;

use Coyote;
use Coyote\Domain\Initials;

readonly class NavigationUserPresenter {
    public function __construct(
        private NavigationUserNotificationPresenter $notifications,
    ) {}

    public function user(): ?\Neon2\NavigationUser {
        if (!auth()->check()) {
            return null;
        }
        /** @var Coyote\User $user */
        $user = auth()->user();
        [$avatarUrl, $avatarInitials] = $this->avatar($user);
        return new \Neon2\NavigationUser(
            $user->name,
            \route('profile', [$user->id]),
            $user->pm_unread,
            $user->notifications_unread,
            $this->notifications->notifications(0, 10),
            $avatarUrl,
            $avatarInitials,
            $user->can('adm-access'),
            'subscribe:user:' . auth()->id());
    }

    private function avatar(Coyote\User $user): array {
        $url = $this->userAvatarUrl($user);
        if ($url) {
            return [$url, null];
        }
        return [null, new Initials()->of($user->name)];
    }

    private function userAvatarUrl(Coyote\User $user): string {
        /** @var Coyote\Services\Media\Factory $factory */
        $factory = app(Coyote\Services\Media\Factory::class);
        return $factory->userAvatar($user->photo)->url();
    }
}
