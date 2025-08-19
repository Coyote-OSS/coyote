<?php
namespace Coyote\Services\Adm\UserContent\UserContentItem;

use Coyote\Job;
use Coyote\User;

class UserJobOffers implements UserContentItem {
    public function count(User $user): int {
        return $user->hasMany(Job::class)->count();
    }

    public function deletedCount(User $user): ?int {
        return null;
    }

    public function massDelete(User $user): void {
        $user->hasMany(Job::class)->delete();
    }
}
