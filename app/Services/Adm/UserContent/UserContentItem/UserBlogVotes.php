<?php
namespace Coyote\Services\Adm\UserContent\UserContentItem;

use Coyote\Microblog;
use Coyote\User;

class UserBlogVotes implements UserContentItem {
    public function count(User $user): int {
        return $user
            ->hasMany(Microblog\Vote::class)
            ->count();
    }

    public function deletedCount(User $user): ?int {
        return null;
    }

    public function massDelete(User $user): int {
        return $user->hasMany(Microblog\Vote::class)->delete();
    }
}
