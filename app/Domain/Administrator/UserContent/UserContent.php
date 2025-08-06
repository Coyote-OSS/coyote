<?php
namespace Coyote\Domain\Administrator\UserContent;

readonly class UserContent {
    public function __construct(
        public int $topics,
        public int $posts,
        public int $postComments,
        public int $postVotes,
        public int $blogs,
        public int $blogComments,
        public int $microblogVotes,
        public int $flags,
        public int $privateMessages,
        public int $jobOffers,
        public int $reputation,
    ) {}
}
