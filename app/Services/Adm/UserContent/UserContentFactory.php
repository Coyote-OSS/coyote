<?php
namespace Coyote\Services\Adm\UserContent;

use Coyote\Domain\Administrator\UserContent\UserContent;
use Coyote\Post;
use Coyote\Services\Adm\UserContent\UserContentItem\UserContentItem;
use Coyote\Topic;
use Coyote\User;

readonly class UserContentFactory {
    private UserContentItem $posts;
    private UserContentItem $postComments;
    private UserContentItem $blogs;
    private UserContentItem $blogComments;
    private UserContentItem $postVotes;
    private UserContentItem $blogVotes;
    private UserContentItem $flags;
    private UserContentItem $messages;
    private UserContentItem $jobOffers;

    public function __construct(private UserContentItemFactory $factory) {
        $this->posts = $this->factory->create(UserContentItemType::POSTS);
        $this->postComments = $this->factory->create(UserContentItemType::POST_COMMENTS);
        $this->blogs = $this->factory->create(UserContentItemType::BLOGS);
        $this->blogComments = $this->factory->create(UserContentItemType::BLOG_COMMENTS);
        $this->postVotes = $this->factory->create(UserContentItemType::POST_VOTES);
        $this->blogVotes = $this->factory->create(UserContentItemType::BLOG_VOTES);
        $this->flags = $this->factory->create(UserContentItemType::FLAGS);
        $this->messages = $this->factory->create(UserContentItemType::MESSAGES);
        $this->jobOffers = $this->factory->create(UserContentItemType::JOB_OFFERS);
    }

    public function create(User $user): UserContent {
        return new UserContent(
            $this->topicsCount($user),
            $this->posts->count($user),
            $this->postComments->count($user),
            $this->postVotes->count($user),
            $this->blogs->count($user),
            $this->blogComments->count($user),
            $this->blogVotes->count($user),
            $this->flags->count($user),
            $this->messages->count($user),
            $this->jobOffers->count($user),
            $user->reputation,
            $this->posts->deletedCount($user),
            $this->postComments->deletedCount($user),
            $this->blogs->deletedCount($user),
            $this->blogComments->deletedCount($user),
            $this->flags->deletedCount($user));
    }

    private function topicsCount(User $user): int {
        return $user
            ->hasManyThrough(Topic::class, Post::class, 'user_id', 'first_post_id')
            ->count();
    }
}
