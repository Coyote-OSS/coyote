<?php
namespace Coyote\Services\Adm\UserContent\UserContentItem;

use Coyote\Pm;
use Coyote\User;

class UserMessages implements UserContentItem {
    public function count(User $user): int {
        return $user
            ->hasMany(Pm::class)
            ->where('folder', PM::SENTBOX)
            ->count();
    }

    public function deletedCount(User $user): ?int {
        return null;
    }

    public function massDelete(User $user): int {
        throw new \Exception('Not implemented');
    }
}
