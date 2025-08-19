<?php
namespace Coyote\Services\Adm\UserContent;

enum UserContentItemType: string {
    case POSTS = 'deletePosts';
    case POST_COMMENTS = 'deletePostComments';
    case BLOGS = 'deleteBlogs';
    case BLOG_COMMENTS = 'deleteBlogComments';
    case POST_VOTES = 'deletePostVotes';
    case BLOG_VOTES = 'deleteBlogVotes';
    case FLAGS = 'deleteFlags';
    case MESSAGES = 'deleteMessages';
    case JOB_OFFERS = 'deleteJobOffers';
}
