<?php
namespace Tests\Legacy\IntegrationNew\BaseFixture\Forum;

use Carbon\Carbon;

readonly class ModelsDriver {
    private ModelsFactory $models;

    public function __construct() {
        $this->models = new ModelsFactory();
    }

    public function newUser(string $name = null, string $createdAt = null, bool $deleted = false): void {
        $this->models->newUserReturn(name:$name, createdAt:$createdAt, deleted:$deleted);
    }

    /**
     * @deprecated
     */
    public function newUserDeleted(string $name): void {
        $this->newUser(name:$name, deleted:true);
    }

    public function newUserReturnId(
        ?string $name = null,
        ?string $permissionName = null,
        ?string $groupName = null,
        ?string $photoUrl = null,
        ?bool   $deleted = false,
        ?string $visitedAt = null,
    ): int {
        $user = $this->models->newUserReturn(name:$name,
            photoUrl:$photoUrl,
            deleted:$deleted,
            createdAt:'1970-01-01T00:00:00',
            visitedAt:$visitedAt);
        if ($permissionName) {
            $this->models->assignToGroupWithPermission($user, $permissionName);
        }
        if ($groupName) {
            $this->models->assignToGroupWithNameReturnId($user, $groupName);
        }
        return $user->id;
    }

    public function newUserConfirmedEmail(string $email): void {
        $this->models->newUserReturn(email:$email, emailConfirmed:true);
    }

    public function newMicroblog(string $contentMarkdown = null): void {
        $this->models->newMicroblogReturn($contentMarkdown);
    }

    public function newMicroblogDeletedAt(string $contentMarkdown, string $deletedAtNoTz): void {
        $this->models->newMicroblogReturn(content:$contentMarkdown, deletedAt:$deletedAtNoTz);
    }

    public function newMicroblogReturnId(): int {
        return $this->models->newMicroblogReturn()->id;
    }

    public function newPost(string $contentMarkdown): void {
        $this->models->newPostReturn(content:$contentMarkdown);
    }

    public function newPostCreatedAt(string $createdAt): void {
        $this->models->newPostReturn(createdAt:new Carbon($createdAt, 'UTC'));
    }

    public function newPostAuthor(string $authorName): void {
        $this->models->newPostReturn(authorName:$authorName);
    }

    public function newPostAuthorPhoto(string $authorPhotoUrl): void {
        $this->models->newPostReturn(authorPhotoUrl:$authorPhotoUrl);
    }

    public function newPostReturnAuthorId(string $contentMarkdown): int {
        $post = $this->models->newPostReturn($contentMarkdown);
        return $post->user_id;
    }

    public function newPostDeleted(string $contentMarkdown): void {
        $this->newPostDeletedAt($contentMarkdown, '1970-01-01 00:00:00');
    }

    public function newPostDeletedAt(string $contentMarkdown, string $deletedAtNoTz): void {
        $this->models->newPostReturn($contentMarkdown, deletedAt:$deletedAtNoTz);
    }

    public function newPostDeletedReported(string $postContentMarkdown = null, string $reportContent = null): void {
        $post = $this->models->newPostReturn(content:$postContentMarkdown, deletedAt:'1970-01-01 00:00:00');
        $this->models->reportPost($post, $reportContent);
    }

    public function newComment(string $contentMarkdown): void {
        $this->models->newCommentReturn($contentMarkdown);
    }

    public function newPostReported(
        string $postContentMarkdown = null,
        string $reportContent = null): void {
        $post = $this->models->newPostReturn($postContentMarkdown);
        $this->models->reportPost($post, $reportContent);
    }

    public function newPostReportedClosed(string $contentMarkdown): void {
        $post = $this->models->newPostReturn($contentMarkdown);
        $this->models->reportPost($post, null)->delete();
    }

    public function newMicroblogReported(string $microblogContentMarkdown = null, string $reportContent = null): void {
        $microblog = $this->models->newMicroblogReturn($microblogContentMarkdown);
        $this->models->reportMicroblog($microblog, $reportContent);
    }

    public function newMicroblogReportedClosed(string $contentMarkdown): void {
        $microblog = $this->models->newMicroblogReturn($contentMarkdown);
        $this->models->reportMicroblog($microblog, null)->delete();
    }

    public function newCommentReported(): void {
        $this->models->reportComment($this->models->newCommentReturn());
    }

    public function newCommentReportedClosed(string $contentMarkdown): void {
        $this->models->reportComment($this->models->newCommentReturn($contentMarkdown))->delete();
    }
}
