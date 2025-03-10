<?php
namespace Coyote\Repositories\Eloquent;

use Coyote\Feature;
use Coyote\Job;
use Coyote\Repositories\Contracts\JobRepositoryInterface;
use Coyote\Tag;
use Illuminate\Database\Eloquent;
use Illuminate\Database\Query\JoinClause;

/**
 * @method mixed search(\Coyote\Services\Elasticsearch\QueryBuilderInterface $queryBuilder)
 * @method $this withTrashed()
 */
class JobRepository extends Repository implements JobRepositoryInterface
{
    public function model(): string
    {
        return Job::class;
    }

    public function findManyWithOrder(array $ids): Eloquent\Collection
    {
        if (empty($ids)) {
            return Eloquent\Collection::empty();
        }
        $values = [];
        foreach ($ids as $key => $id) {
            $values[] = "($id,$key)";
        }
        $this->applyCriteria();
        $result = $this
            ->model
            ->addSelect('jobs.*')
            ->join($this->raw('(VALUES ' . implode(',', $values) . ') AS x (id, ordering)'), 'jobs.id', '=', 'x.id')
            ->orderBy('x.ordering')
            ->get();
        $this->resetModel();

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function published($userId)
    {
        $this->applyCriteria();

        $result = $this
            ->model
            ->select('jobs.*')
            ->where('jobs.user_id', $userId)
            ->orderBy('jobs.id', 'DESC')
            ->paginate();

        $this->resetModel();
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function subscribes($userId)
    {
        $this->applyCriteria();

        $result = $this
            ->model
            ->select(['jobs.*'])
            ->join('subscriptions', function (JoinClause $join) use ($userId) {
                $join->on('subscriptions.resource_id', '=', 'jobs.id')->where('subscriptions.resource_type', Job::class)->where('subscriptions.user_id', $userId);
            })
            ->get();

        $this->resetModel();

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function getDefaultFeatures($userId)
    {
        $sub = $this->toSql(
            $this->model->select('id')->where('user_id', $userId)->orderBy('id', 'DESC')->limit(1),
        );

        return $this
            ->app
            ->make(Feature::class)
            ->selectRaw(
                'features.*, COALESCE(job_features.checked, 0) AS checked, COALESCE(job_features.value, \'\') AS value',
            )
            ->leftJoin('job_features', function (JoinClause $join) use ($sub) {
                return $join->on('job_id', '=', $this->raw("($sub)"))->on('feature_id', '=', 'features.id');
            })
            ->orderBy('order')
            ->get();
    }

    /**
     * @inheritdoc
     */
    public function getTagSuggestions(array $tags): array
    {
        return (new Tag())
            ->select(['t.name'])
            ->join('tag_resources', 'tag_resources.tag_id', '=', 'tags.id')
            ->join('tag_resources AS tr', 'tr.resource_id', '=', 'tag_resources.resource_id')
            ->join('tags AS t', 't.id', 'tr.tag_id')
            ->whereIn('tags.name', $tags)
            ->whereRaw('t.id != tags.id')
            ->groupBy('t.name')
            ->orderByRaw('COUNT(*) DESC')
            ->limit(5)
            ->pluck('name')
            ->toArray();
    }
}
