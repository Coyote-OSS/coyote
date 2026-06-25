<?php
namespace Tests\Legacy\IntegrationNew\Canonical\Topic\Fixture\PostEdit;

use Coyote\Forum;
use Coyote\Post;
use Coyote\Topic;
use Tests\Legacy\IntegrationNew\BaseFixture;

trait Models {
    use BaseFixture\Forum\Store;
    use BaseFixture\Forum\Models;

    function newPost(): array {
        $topic = $this->storeThread(new Forum, new Topic);
        return [$topic->id, $topic->firstPost->id];
    }

    function newPostWithAuthor(string $forumSlug): array {
        $topic = $this->storeThread(
            new Forum(['slug' => $forumSlug]),
            new Topic,
            new Post(['user_id' => $this->driver->newUserReturnId(emailConfirmed:true)]));
        return [$topic->id, $topic->firstPost->id, $topic->firstPost->user];
    }
}
