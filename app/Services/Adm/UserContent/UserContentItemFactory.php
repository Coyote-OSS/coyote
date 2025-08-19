<?php
namespace Coyote\Services\Adm\UserContent;

use Coyote\Services\Adm\UserContent\UserContentItem\UserBlogComments;
use Coyote\Services\Adm\UserContent\UserContentItem\UserBlogs;
use Coyote\Services\Adm\UserContent\UserContentItem\UserBlogVotes;
use Coyote\Services\Adm\UserContent\UserContentItem\UserContentItem;
use Coyote\Services\Adm\UserContent\UserContentItem\UserFlags;
use Coyote\Services\Adm\UserContent\UserContentItem\UserJobOffers;
use Coyote\Services\Adm\UserContent\UserContentItem\UserMessages;
use Coyote\Services\Adm\UserContent\UserContentItem\UserPostComments;
use Coyote\Services\Adm\UserContent\UserContentItem\UserPosts;
use Coyote\Services\Adm\UserContent\UserContentItem\UserPostVotes;

class UserContentItemFactory {
    public function create(UserContentItemType $type): UserContentItem {
        return match ($type) {
            UserContentItemType::POSTS         => new UserPosts(),
            UserContentItemType::POST_COMMENTS => new UserPostComments(),
            UserContentItemType::BLOGS         => new UserBlogs(),
            UserContentItemType::BLOG_COMMENTS => new UserBlogComments(),
            UserContentItemType::POST_VOTES    => new UserPostVotes(),
            UserContentItemType::BLOG_VOTES    => new UserBlogVotes(),
            UserContentItemType::FLAGS         => new UserFlags(),
            UserContentItemType::MESSAGES      => new UserMessages(),
            UserContentItemType::JOB_OFFERS    => new UserJobOffers(),
        };
    }
}
