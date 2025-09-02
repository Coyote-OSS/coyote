<?php
namespace Coyote\Repositories\Criteria;

use Coyote\Repositories\Contracts\RepositoryInterface;
use Illuminate\Database\Eloquent;

class SkipIncognitoUsers extends Criteria {
    /**
     * @param Eloquent\Builder $model
     * @param RepositoryInterface $repository
     * @return Eloquent\Builder
     */
    public function apply($model, RepositoryInterface $repository): Eloquent\Builder {
        return $model->whereHas(
            'user',
            fn(Eloquent\Builder $sub) => $sub->where('is_incognito', false));
    }
}
