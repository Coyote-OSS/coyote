<?php
namespace Coyote\Services\Adm\UserContent\UserContentItem;

use Coyote\Microblog;
use Coyote\User;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserBlogs implements UserContentItem {
    public function count(User $user): int {
        return $this->blogs($user)->count();
    }

    public function deletedCount(User $user): ?int {
        return $this->blogs($user)->onlyTrashed()->count();
    }

    public function massDelete(User $user): int {
        return $this->blogs($user)->delete();
    }

    private function blogs(User $user): HasMany {
        return $user
            ->hasMany(Microblog::class)
            ->whereNull('parent_id');
    }
}
