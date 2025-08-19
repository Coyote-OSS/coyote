<?php
namespace Coyote\Services\Adm\UserContent\UserContentItem;

use Coyote\Microblog;
use Coyote\User;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserBlogComments implements UserContentItem {
    public function count(User $user): int {
        return $this->blogComments($user)->count();
    }

    public function deletedCount(User $user): ?int {
        return $this->blogComments($user)->onlyTrashed()->count();
    }

    public function massDelete(User $user): int {
        return $this->blogComments($user)->delete();
    }

    private function blogComments(User $user): HasMany {
        return $user
            ->hasMany(Microblog::class)
            ->whereNotNull('parent_id');
    }
}
