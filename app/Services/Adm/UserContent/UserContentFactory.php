<?php
namespace Coyote\Services\Adm\UserContent;

use Coyote\Domain\Administrator\UserContent\UserContent;
use Coyote\Post;
use Coyote\Topic;
use Coyote\User;

readonly class UserContentFactory {
    public function __construct(private UserContentItemFactory $factory) {}

    public function create(User $user): UserContent {
        $posts = $this->factory->create('deletePosts');
        $postComments = $this->factory->create('deletePostComments');
        $postVotes = $this->factory->create('deletePostVotes');
        $blogs = $this->factory->create('deleteBlogs');
        $blogComments = $this->factory->create('deleteBlogComments');
        $blogVotes = $this->factory->create('deleteBlogVotes');
        $flags = $this->factory->create('deleteFlags');
        $messages = $this->factory->create('deleteMessages');
        $jobOffers = $this->factory->create('deleteJobOffers');

        return new UserContent(
            $this->topicsCount($user),
            $posts->count($user),
            $postComments->count($user),
            $postVotes->count($user),
            $blogs->count($user),
            $blogComments->count($user),
            $blogVotes->count($user),
            $flags->count($user),
            $messages->count($user),
            $jobOffers->count($user),
            $user->reputation,
            $posts->deletedCount($user),
            $postComments->deletedCount($user),
            $blogs->deletedCount($user),
            $blogComments->deletedCount($user),
            $flags->deletedCount($user));
    }

    private function topicsCount(User $user): int {
        return $user
            ->hasManyThrough(Topic::class, Post::class, 'user_id', 'first_post_id')
            ->count();
    }
}
