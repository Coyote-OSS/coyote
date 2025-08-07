<?php

namespace Coyote\Models\Scopes;

use Coyote\Services\Forum\UserDefined;
use Coyote\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class UserRelationsScope implements Scope {
    private UserDefined $userDefined;

    public function __construct(UserDefined $userDefined) {
        $this->userDefined = $userDefined;
    }

    public function apply(Builder $builder, Model $model): void {
        if (empty($this->getExcludedUsers())) {
            return;
        }

        $builder->whereNotIn($model->getTable() . '.user_id', $this->getExcludedUsers());
    }

    protected function getExcludedUsers(): ?array {
        static $excluded;

        if (!auth()->check() || $excluded !== null) {
            return $excluded;
        }
        /** @var User $user */
        $user = auth()->user();
        return $this->blockedUserIds($user);
    }

    private function blockedUserIds(User $user): array {
        $blockedRelations = array_filter(
            $this->userDefined->followers($user),
            fn(array $relation): bool => $relation['is_blocked'] === true);
        return \array_column($blockedRelations, 'user_id');
    }
}
