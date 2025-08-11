<?php
namespace Coyote\Services\Adm;

use Coyote\Flag;
use Coyote\Job;
use Coyote\Microblog;
use Coyote\Post;
use Coyote\User;

class UserContentDeleteService {
    public function deletePosts(User $user): void {
        $user->hasMany(Post::class)
            ->join('topics', 'topics.id', '=', 'posts.topic_id')
            ->whereColumn('posts.id', '!=', 'topics.first_post_id')
            ->delete();
    }

    public function deletePostComments(User $user): void {
        $user->hasMany(Post\Comment::class)->delete();
    }

    public function deleteBlogs(User $user): void {
        $user
            ->hasMany(Microblog::class)
            ->whereNull('parent_id')
            ->delete();
    }

    public function deleteBlogComments(User $user): void {
        $user
            ->hasMany(Microblog::class)
            ->whereNotNull('parent_id')
            ->delete();
    }

    public function deletePostVotes(User $user): void {
        $user->hasMany(Post\Vote::class)->delete();
    }

    public function deleteBlogVotes(User $user): void {
        $user->hasMany(Microblog\Vote::class)->delete();
    }

    public function deleteFlags(User $user): void {
        $user->hasMany(Flag::class)->delete();
    }

    public function deleteMessages(User $user): void {}

    public function deleteJobOffers(User $user): void {
        $user->hasMany(Job::class)->delete();
    }
}
