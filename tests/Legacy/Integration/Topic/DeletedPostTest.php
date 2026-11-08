<?php
namespace Tests\Legacy\Integration\Topic;

use Coyote\Forum;
use Coyote\Post;
use Coyote\Topic;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Legacy\Integration\BaseFixture;
use Tests\Legacy\Integration\BaseFixture\Server;
use Tests\Legacy\Integration\Topic\Fixture\SpaView;

class DeletedPostTest extends TestCase {
    use BaseFixture\Forum\Store, Server\Http;

    private Topic $topic;
    private Post $deletedPost;

    #[Test]
    public function deletedPost_isVisibleToGuest_withoutContent(): void {
        $this->newTopicWithDeletedPost('Secret content of removed post');
        $html = $this->topicHtml();
        $this->assertStringNotContainsString('Secret content of removed post', $html);
    }

    #[Test]
    public function deletedPost_isVisibleToGuest_withRemovalInformation(): void {
        $this->newTopicWithDeletedPost('irrelevant', authorName:'Mark', deleterName:'Moderator', reason:'Spam');
        $post = $this->deletedPostResource();
        $this->assertSame('obscured', $post['type']);
        $this->assertSame('Mark', $post['user']['name']);
        $this->assertSame('Moderator', $post['deleter_name']);
        $this->assertSame('Spam', $post['delete_reason']);
        $this->assertNotNull($post['created_at']);
        $this->assertNotNull($post['deleted_at']);
    }

    #[Test]
    public function deletedPost_deletedByAuthor_hasDeleterSameAsAuthor(): void {
        $this->newTopicWithDeletedPost('irrelevant', deletedByAuthor:true);
        $post = $this->deletedPostResource();
        $this->assertSame($post['user_id'], $post['deleter_id']);
    }

    #[Test]
    public function deletedPost_deletedByModerator_hasDeleterOtherThanAuthor(): void {
        $this->newTopicWithDeletedPost('irrelevant');
        $post = $this->deletedPostResource();
        $this->assertNotSame($post['user_id'], $post['deleter_id']);
    }

    #[Test]
    public function deletedPost_isVisibleToGuest_withoutCommentsAndScore(): void {
        $this->newTopicWithDeletedPost('irrelevant');
        $post = $this->deletedPostResource();
        $this->assertArrayNotHasKey('text', $post);
        $this->assertArrayNotHasKey('html', $post);
        $this->assertArrayNotHasKey('comments', $post);
        $this->assertArrayNotHasKey('score', $post);
    }

    private function newTopicWithDeletedPost(
        string $content,
        ?string $authorName = null,
        ?string $deleterName = null,
        ?string $reason = null,
        bool    $deletedByAuthor = false,
    ): void {
        $factory = new BaseFixture\Forum\ModelsFactory();
        $this->topic = $this->storeThread(new Forum, new Topic);
        $post = new Post([
            'text'     => $content,
            'ip'       => 'irrelevant',
            'topic_id' => $this->topic->id,
            'forum_id' => $this->topic->forum_id,
            'user_id'  => $factory->newUserReturn(name:$authorName)->id,
        ]);
        $post->save();
        $post->deleter_id = $deletedByAuthor
            ? $post->user_id
            : $factory->newUserReturn(name:$deleterName)->id;
        $post->delete_reason = $reason;
        $post->deleted_at = '2020-01-01 00:00:00';
        $post->save();
        $this->deletedPost = $post;
    }

    private function deletedPostResource(): array {
        $view = new SpaView($this->topicHtml());
        $posts = $view->jsVariables()['pagination']['data'];
        return $posts[$this->deletedPost->id];
    }

    private function topicHtml(): string {
        $forum = $this->topic->forum;
        return $this->server
            ->get("/Forum/{$forum->slug}/{$this->topic->id}-{$this->topic->slug}")
            ->assertSuccessful()
            ->content();
    }
}
