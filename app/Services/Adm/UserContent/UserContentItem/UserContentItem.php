<?php
namespace Coyote\Services\Adm\UserContent\UserContentItem;

use Coyote\User;

interface UserContentItem {
    public function count(User $user): int;

    public function deletedCount(User $user): ?int;

    public function massDelete(User $user): int;
}
