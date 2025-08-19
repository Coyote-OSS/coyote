<?php
namespace Coyote\Services\Adm\UserContent\UserContentItem;

use Coyote\Flag;
use Coyote\User;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserFlags implements UserContentItem {
    public function count(User $user): int {
        return $this->flags($user)->count();
    }

    public function deletedCount(User $user): ?int {
        return $this->flags($user)->withTrashed()->count();
    }

    public function massDelete(User $user): void {
        $this->flags($user)->delete();
    }

    private function flags(User $user): HasMany {
        return $user->hasMany(Flag::class);
    }
}
