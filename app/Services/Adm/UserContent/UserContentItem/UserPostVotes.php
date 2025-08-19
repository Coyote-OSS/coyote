<?php
namespace Coyote\Services\Adm\UserContent\UserContentItem;

use Coyote\Post;
use Coyote\User;

class UserPostVotes implements UserContentItem {
    public function count(User $user): int {
        return $user->hasMany(Post\Vote::class)->count();
    }

    public function deletedCount(User $user): ?int {
        return null;
    }

    public function massDelete(User $user): int {
        return $user->hasMany(Post\Vote::class)->delete();
    }
}
