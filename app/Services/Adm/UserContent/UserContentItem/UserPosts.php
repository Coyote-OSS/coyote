<?php
namespace Coyote\Services\Adm\UserContent\UserContentItem;

use Coyote\Post;
use Coyote\User;

class UserPosts implements UserContentItem {
    public function count(User $user): int {
        return $user->hasMany(Post::class)->count();
    }

    public function deletedCount(User $user): ?int {
        return $user->hasMany(Post::class)->onlyTrashed()->count();
    }

    public function massDelete(User $user): int {
        return $user->hasMany(Post::class)
            ->join('topics', 'topics.id', '=', 'posts.topic_id')
            ->whereColumn('posts.id', '!=', 'topics.first_post_id')
            ->delete();
    }
}
