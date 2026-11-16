<?php
namespace Tests\Integration\Wiki;

use Coyote\Http\Controllers\Wiki\CommentController;
use Coyote\Wiki;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Legacy\Integration\BaseFixture\Forum\ModelsDriver;
use Tests\Legacy\Integration\BaseFixture\Server;

/**
 * @see https://github.com/Coyote-OSS/coyote/issues/818
 */
#[CoversClass(CommentController::class)]
class CommentControllerTest extends TestCase {
    use Server\Laravel\Transactional;
    use Server\Http;

    private ModelsDriver $models;

    #[Before]
    public function initializeModels(): void {
        $this->models = new ModelsDriver();
    }

    #[Test]
    public function editingComment_isAllowed_forItsAuthor(): void {
        // given a wiki page with a comment of mine
        $authorId = $this->models->newUserReturnId();
        [$pathId, $commentId] = $this->newWikiPageWithComment($authorId, 'Original comment');
        // when I edit my comment
        $this->server->loginById($authorId);
        $response = $this->httpEditComment($pathId, $commentId, 'Edited by author');
        // then the comment is changed
        $response->assertOk();
        $this->assertSame('Edited by author', $this->commentText($commentId));
    }

    #[Test]
    public function editingComment_isForbidden_forOtherUser(): void {
        // given a wiki page with a comment of another user
        $authorId = $this->models->newUserReturnId();
        [$pathId, $commentId] = $this->newWikiPageWithComment($authorId, 'Original comment');
        // and I am a user without any permissions
        $this->server->loginById($this->models->newUserReturnId());
        // when I attempt to edit that comment
        $response = $this->httpEditComment($pathId, $commentId, 'Edited by intruder');
        // then the request is rejected
        $response->assertForbidden();
    }

    #[Test]
    public function editingComment_byOtherUser_doesNotChangeComment(): void {
        // given a wiki page with a comment of another user
        $authorId = $this->models->newUserReturnId();
        [$pathId, $commentId] = $this->newWikiPageWithComment($authorId, 'Original comment');
        // and I am a user without any permissions
        $this->server->loginById($this->models->newUserReturnId());
        // when I attempt to edit that comment
        $this->httpEditComment($pathId, $commentId, 'Edited by intruder');
        // then the comment remains unchanged
        $this->assertSame('Original comment', $this->commentText($commentId));
    }

    private function httpEditComment(int $pathId, int $commentId, string $text): TestResponse {
        // submit a valid csrf token, so the request reaches the controller
        // regardless of whether csrf protection is enabled in the environment
        return $this->laravel
            ->withSession(['_token' => 'csrf-token'])
            ->post("/Comment/Save/$pathId/$commentId", ['text' => $text, '_token' => 'csrf-token']);
    }

    /**
     * @return int[] [pathId, commentId]
     */
    private function newWikiPageWithComment(int $authorId, string $text): array {
        $page = new Wiki\Page(['title' => 'Wiki page', 'text' => 'Content of wiki page']);
        $page->save();
        $path = $page->createPath(new Wiki\Path(), $page->slug);
        $comment = new Wiki\Comment(['user_id' => $authorId, 'text' => $text, 'ip' => '127.0.0.1']);
        $comment->wiki_id = $page->id;
        $comment->save();
        return [$path->path_id, $comment->id];
    }

    private function commentText(int $commentId): string {
        return Wiki\Comment::query()->findOrFail($commentId)->text;
    }
}
