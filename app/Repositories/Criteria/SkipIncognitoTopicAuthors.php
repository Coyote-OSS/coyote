<?php
namespace Coyote\Repositories\Criteria;

use Coyote\Repositories\Contracts\RepositoryInterface;
use Illuminate\Database\Eloquent;

class SkipIncognitoTopicAuthors extends Criteria {
    public function __construct(private ?int $userId) {}

    /**
     * @param Eloquent\Builder $model
     * @param RepositoryInterface $repository
     * @return Eloquent\Builder
     */
    public function apply($model, RepositoryInterface $repository): Eloquent\Builder {
        return $model
            ->leftJoin('posts', 'posts.id', '=', 'topics.first_post_id')
            ->leftJoin('users', 'users.id', '=', 'posts.user_id')
            ->where(function (Eloquent\Builder $builder) {
                return $builder->where('users.is_incognito', '=', false)
                    ->orWhere('users.id', '=', $this->userId);
            });
    }
}
