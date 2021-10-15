<?php

namespace Coyote\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Query\JoinClause;

trait TrackForum
{
    /**
     * Scope a query to only given user id.
     *
     * @param Builder $builder
     * @param string|null $guestId
     * @param string $alias
     * @return Builder
     */
    public function scopeWithForumMarkTime(Builder $builder, ?string $guestId, string $alias = 'read_at')
    {
        if ($guestId === null) {
            return $builder;
        }

        return $builder
            ->addSelect(new Expression("COALESCE(forum_track.marked_at, guests.created_at) AS $alias"))
            ->leftJoin('forum_track', function (JoinClause $join) use ($guestId) {
                $join
                    ->on('forum_track.forum_id', '=', 'forums.id')
                    ->where('forum_track.guest_id', '=', $guestId);
            })
            ->leftJoin('guests', function (JoinClause $join) use ($guestId) {
                $join->where('guests.id', '=', $guestId);
            });
    }
}
