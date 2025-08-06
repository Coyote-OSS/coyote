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

class UserContentService {
    public function userContent(User $user): UserContent {
        return new UserContent(
            $user
                ->hasManyThrough(Topic::class, Post::class, 'user_id', 'first_post_id')
                ->count(),
            $user->hasMany(Post::class)->count(),
            $user->hasMany(Post\Comment::class)->count(),
            $user->hasMany(Post\Vote::class)->count(),
            $user
                ->hasMany(Microblog::class)
                ->whereNull('parent_id')
                ->count(),
            $user
                ->hasMany(Microblog::class)
                ->whereNotNull('parent_id')
                ->count(),
            $user->hasMany(Microblog\Vote::class)->count(),
            $user->hasMany(Flag::class)->count(),
            $user
                ->hasMany(Pm::class)
                ->where('folder', PM::SENTBOX)
                ->count(),
            $user->hasMany(Job::class)->count(),
            $user->reputation);
    }
}
