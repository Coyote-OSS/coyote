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
    public function create(string $contentType): UserContentItem {
        return match ($contentType) {
            'deletePosts'        => new UserPosts(),
            'deletePostComments' => new UserPostComments(),
            'deleteBlogs'        => new UserBlogs(),
            'deleteBlogComments' => new UserBlogComments(),
            'deletePostVotes'    => new UserPostVotes(),
            'deleteBlogVotes'    => new UserBlogVotes(),
            'deleteFlags'        => new UserFlags(),
            'deleteMessages'     => new UserMessages(),
            'deleteJobOffers'    => new UserJobOffers(),
        };
    }
}
