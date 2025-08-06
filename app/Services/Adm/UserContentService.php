<?php
namespace Coyote\Services\Adm;

use Coyote\Domain\Administrator\UserContent\UserContent;
use Coyote\Flag;
use Coyote\Job;
use Coyote\Microblog;
use Coyote\Pm;
use Coyote\Post;
use Coyote\Topic;
use Coyote\User;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserContentService {
    public function userContent(User $user): UserContent {
        return new UserContent(
            $user
                ->hasManyThrough(Topic::class, Post::class, 'user_id', 'first_post_id')
                ->count(),
            $this->posts($user)->count(),
            $this->postComments($user)->count(),
            $user->hasMany(Post\Vote::class)->count(),
            $this->blogs($user)->count(),
            $this->blogComments($user)->count(),
            $user->hasMany(Microblog\Vote::class)->count(),
            $this->flags($user)->count(),
            $user
                ->hasMany(Pm::class)
                ->where('folder', PM::SENTBOX)
                ->count(),
            $user->hasMany(Job::class)->count(),
            $user->reputation,
            $this->posts($user)->onlyTrashed()->count(),
            $this->postComments($user)->onlyTrashed()->count(),
            $this->blogs($user)->onlyTrashed()->count(),
            $this->blogComments($user)->onlyTrashed()->count(),
            $this->flags($user)->withTrashed()->count());
    }

    private function blogs(User $user): HasMany {
        return $user
            ->hasMany(Microblog::class)
            ->whereNull('parent_id');
    }

    private function blogComments(User $user): HasMany {
        return $user
            ->hasMany(Microblog::class)
            ->whereNotNull('parent_id');
    }

    private function posts(User $user): HasMany {
        return $user->hasMany(Post::class);
    }

    private function postComments(User $user): HasMany {
        return $user->hasMany(Post\Comment::class);
    }

    private function flags(User $user): HasMany {
        return $user->hasMany(Flag::class);
    }
}
