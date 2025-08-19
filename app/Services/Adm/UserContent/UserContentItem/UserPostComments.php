<?php
namespace Coyote\Services\Adm\UserContent\UserContentItem;

use Coyote\Post;
use Coyote\User;

class UserPostComments implements UserContentItem {
    public function count(User $user): int {
        return $user->hasMany(Post\Comment::class)->count();
    }

    public function deletedCount(User $user): ?int {
        return $user->hasMany(Post\Comment::class)->onlyTrashed()->count();
    }

    public function massDelete(User $user): void {
        $user->hasMany(Post\Comment::class)->delete();
    }
}
