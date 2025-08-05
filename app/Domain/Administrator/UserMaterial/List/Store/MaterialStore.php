<?php
namespace Coyote\Domain\Administrator\UserMaterial\List\Store;

use Carbon\Carbon;
use Coyote\Microblog;
use Coyote\Models\Flag\Resource;
use Coyote\Post;
use Coyote\Services\UrlBuilder;
use Illuminate\Database\Eloquent;
use Illuminate\Database\Query;

class MaterialStore {
    public function fetch(MaterialRequest $request): MaterialResult {
        /** @var Eloquent\Builder $query */
        $query = $this->queryByType($request->type)->withTrashed();

        $this->whereTrashed($query, $request->deleted);

        $materialTable = $query->getModel()->getTable();
        $flagResourceTable = (new Resource)->getTable();

        if ($request->reported !== null) {
            $query->whereExists(
                callback:fn(Query\Builder $query) => $query
                    ->from($flagResourceTable)
                    ->whereColumn("$flagResourceTable.resource_id", "$materialTable.id")
                    ->where("$flagResourceTable.resource_type", $this->resourceClassByType($request->type)),
                not:!$request->reported);
        }
        if ($request->authorId !== null) {
            $query->where('user_id', $request->authorId);
        }
        if ($request->reportOpen !== null) {
            $query->whereRelation('flags', function (Eloquent\Builder $query) use ($request) {
                $this->whereTrashed($query, !$request->reportOpen);
            });
        }
        $materials = $query
            ->clone()
            ->select("$materialTable.*", 'users.name AS username', 'users.photo AS user_photo')
            ->leftJoin('users', 'users.id', '=', 'user_id')
            ->offset(($request->page - 1) * $request->pageSize)
            ->limit($request->pageSize)
            ->orderBy('created_at', 'DESC')
            ->orderBy('id', 'DESC')
            ->get()
            ->map(fn(Post|Microblog|Post\Comment $material) => new Material(
                $material->id,
                $request->type,
                $material->created_at,
                $this->deletedAt($material),
                $this->parentDeletedAt($material),
                $this->microblogParentId($material),
                $material->username ?? '',
                $material->user_photo,
                $material->text,
                $material->flags()->withTrashed()->exists(),
                $material->flags()->exists(),
                $this->href($material),
            ))
            ->toArray();

        return new MaterialResult(
            $materials,
            $query->count(),
        );
    }

    private function queryByType(string $type): Eloquent\Builder {
        if ($type === 'comment') {
            return Post\Comment::query();
        }
        if ($type === 'post') {
            return Post::query();
        }
        return Microblog::query();
    }

    private function resourceClassByType(string $type): string {
        if ($type === 'comment') {
            return Post\Comment::class;
        }
        if ($type === 'post') {
            return Post::class;
        }
        return Microblog::class;
    }

    private function deletedAt(Post|Post\Comment|Microblog $material): ?Carbon {
        if (\is_string($material->deleted_at)) {
            return new Carbon($material->deleted_at, 'Europe/Warsaw');
        }
        return $material->deleted_at;
    }

    private function parentDeletedAt(Post|Post\Comment|Microblog $material): ?Carbon {
        if ($material instanceof Post\Comment) {
            return $this->deletedAt($material->post);
        }
        return null;
    }

    private function whereTrashed(Eloquent\Builder $query, ?bool $deleted): void {
        if ($deleted === true) {
            $query->onlyTrashed();
        }
        if ($deleted === false) {
            $query->withoutTrashed();
        }
    }

    private function microblogParentId(Post|Post\Comment|Microblog $material): ?int {
        if ($material instanceof Microblog) {
            return $material->parent_id;
        }
        return null;
    }

    private function href(Post|Post\Comment|Microblog $material): string {
        if ($material instanceof Microblog) {
            return $this->microblogHref($material);
        }
        if ($material instanceof Post) {
            return UrlBuilder::post($material);
        }
        if ($material instanceof Post\Comment) {
            return UrlBuilder::postComment($material);
        }
    }

    private function microblogHref(Microblog $material): string {
        if ($material->parentId) {
            return route('microblog.view', [$material->parentId]) . '#comment-' . $material->id;
        }
        return route('microblog.view', [$material->parentId ?? $material->id]);
    }
}
