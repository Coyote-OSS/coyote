<?php
namespace Tests\Legacy\IntegrationNew\BaseFixture\Forum;

use Coyote\Flag;
use Coyote\Microblog;
use Coyote\Post;
use Coyote\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\Concerns;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Legacy\IntegrationNew\BaseFixture;

class ModelsDriverTest extends TestCase
{
    use BaseFixture\Server\Laravel\Transactional;
    use Concerns\InteractsWithDatabase;

    private ModelsDriver $models;
    private Application $app;

    #[Before]
    public function initializeModels(): void
    {
        $this->models = new ModelsDriver();
        $this->app = $this->laravel->app;
    }

    #[Before]
    public function removeFlags(): void
    {
        Flag::query()->delete();
    }

    #[Test]
    public function newUser(): void
    {
        $users = $this->usersCount();
        $this->models->newUser();
        $this->assertSame($users + 1, $this->usersCount(),
            'Failed to assert that a new user was created.');
    }

    #[Test]
    public function newUserReturnId(): void
    {
        $id = $this->models->newUserReturnId();
        $this->assertDatabaseHas('users', ['id' => $id]);
    }

    #[Test]
    public function newUserName(): void
    {
        $this->models->newUser('Mark');
        $this->assertDatabaseHas('users', ['name' => 'Mark']);
    }

    #[Test]
    public function newUserDeleted(): void
    {
        $this->models->newUserDeleted('Mark');
        $this->assertSoftDeleted('users', ['name' => 'Mark']);
    }

    #[Test]
    public function newUserDeletedNamedArgumentTrue(): void
    {
        $this->models->newUser('Mark', deleted:true);
        $this->assertSoftDeleted('users', ['name' => 'Mark']);
    }

    #[Test]
    public function newUserDeletedNamedArgumentFalse(): void
    {
        $this->models->newUser('Mark', deleted:false); // default value in test by design
        $this->assertDatabaseHas('users', ['name' => 'Mark']);
    }

    #[Test]
    public function newUserConfirmedEmail(): void
    {
        $this->models->newUserConfirmedEmail('mail');
        $this->assertDatabaseHas('users', ['email' => 'mail', 'is_confirm' => true]);
    }

    #[Test]
    public function newUserMany(): void
    {
        $users = $this->usersCount();
        $this->models->newUser();
        $this->models->newUser();
        $this->assertSame($users + 2, $this->usersCount(),
            'Failed to assert that two new users were created.');
    }

    #[Test]
    public function newMicroblogContent(): void
    {
        $this->models->newMicroblog('content');
        $this->assertDatabaseHas('microblogs', ['text' => 'content']);
    }

    #[Test]
    public function newMicroblogReturnId(): void
    {
        $id = $this->models->newMicroblogReturnId();
        $this->assertDatabaseHas('microblogs', ['id' => $id]);
    }

    #[Test]
    public function newMicroblogDeletedAt(): void
    {
        $this->models->newMicroblogDeletedAt('content', '2005-04-02 21:37:00');
        $this->assertDatabaseHas('microblogs', [
            'text'       => 'content',
            'deleted_at' => '2005-04-02 21:37:00',
        ]);
    }

    private function usersCount(): int
    {
        return User::query()->count();
    }

    #[Test]
    public function newPostReport(): void
    {
        $this->models->newPostReported(reportContent:'report');
        $this->assertDatabaseHas('flags', ['text' => 'report']);
    }

    #[Test]
    public function newPostReported(): void
    {
        $this->models->newPostReported(reportContent:'report');
        $this->assertTrue(Post::query()->whereHas('flags')->exists());
    }

    #[Test]
    public function newPostReportedContent(): void
    {
        $this->models->newPostReported(postContentMarkdown:'content');
        $this->assertTrue(Post::query()->where(['text' => 'content'])
            ->whereHas('flags')
            ->exists());
    }

    #[Test]
    public function newPostDeletedReported(): void
    {
        $this->models->newPostDeletedReported(reportContent:'report');
        $this->assertTrue($this->reportPost('report')->trashed());
    }

    private function reportPost(string $reportContent): Post
    {
        /** @var Flag $model */
        $model = Flag::query()->where('text', $reportContent)->first();
        /** @var Post $post */
        $post = $model->posts()->withTrashed()->first();
        return $post;
    }

    #[Test]
    #[DoesNotPerformAssertions]
    public function newPostReportedMany(): void
    {
        $this->models->newPostReported();
        $this->models->newPostReported();
    }

    #[Test]
    public function newPostReportedReportContent(): void
    {
        $this->models->newPostReported('content', 'report text');
        $this->assertSame(
            'report text',
            Post::query()->where(['text' => 'content'])->first()->flags->pluck('text')->first());
    }

    #[Test]
    public function newMicroblogReported(): void
    {
        $reportedPosts = $this->reportedMicroblogsCount();
        $this->models->newMicroblogReported();
        $this->assertSame($reportedPosts + 1, $this->reportedMicroblogsCount());
    }

    #[Test]
    public function newMicroblogReportedReportContent(): void
    {
        $this->models->newMicroblogReported(microblogContentMarkdown:'content', reportContent:'report text');
        $this->assertSame(
            'report text',
            Microblog::query()->where(['text' => 'content'])->first()->flags->pluck('text')->first());
    }

    private function reportedMicroblogsCount(): int
    {
        return Microblog::query()->whereHas('flags')->count();
    }

    #[Test]
    public function newPostReportedClosed(): void
    {
        $this->models->newPostReportedClosed(contentMarkdown:'content');
        $this->assertTrue($this->postHasClosedReport('content'));
    }

    #[Test]
    public function newPostDeletedReported_postIsDeleted(): void
    {
        $this->models->newPostDeletedReported(postContentMarkdown:'content');
        $this->assertSoftDeleted('posts', [
            'text' => 'content',
        ]);
    }

    #[Test]
    public function newPostDeletedReported_reportHasContent(): void
    {
        $this->models->newPostDeletedReported(reportContent:'reported text');
        $this->assertDatabaseHas('flags', [
            'text' => 'reported text',
        ]);
    }

    #[Test]
    public function newMicroblogReportedClosed(): void
    {
        $this->models->newMicroblogReportedClosed(contentMarkdown:'content');
        $this->assertTrue($this->microblogHasClosedReport('content'));
    }

    #[Test]
    public function newCommentReportedClosed(): void
    {
        $this->models->newCommentReportedClosed(contentMarkdown:'content');
        $this->assertTrue($this->commentHasClosedReport('content'));
    }

    private function postHasClosedReport(string $content): bool
    {
        /** @var Post $post */
        $post = Post::query()->where(['text' => $content])->firstOrFail();
        /** @var Flag $flag */
        [$flag] = $post->flags()->withTrashed()->get();
        return $flag->trashed();
    }

    private function microblogHasClosedReport(string $content): bool
    {
        /** @var Microblog $microblog */
        $microblog = Microblog::query()->where(['text' => $content])->firstOrFail();
        /** @var Flag $flag */
        [$flag] = $microblog->flags()->withTrashed()->get();
        return $flag->trashed();
    }

    private function commentHasClosedReport(string $content): bool
    {
        /** @var Post\Comment $comment */
        $comment = Post\Comment::query()->where(['text' => $content])->firstOrFail();
        /** @var Flag $flag */
        [$flag] = $comment->flags()->withTrashed()->get();
        return $flag->trashed();
    }

    #[Test]
    public function newUserWithPermission(): void
    {
        $userId = $this->models->newUserReturnId(permissionName:'forum-delete');
        $this->assertUserCan(true, $userId, 'forum-delete');
    }

    #[Test]
    public function newUserWithPermissionNoOther(): void
    {
        $userId = $this->models->newUserReturnId(permissionName:'job-update');
        $this->assertUserCan(false, $userId, 'forum-delete');
    }

    #[Test]
    public function newUserWithGroupName(): void
    {
        $userId = $this->models->newUserReturnId(groupName:'Writer');
        $this->assertDatabaseHas('users', [
            'id'         => $userId,
            'group_name' => 'Writer',
        ]);
    }

    #[Test]
    public function newUserWithAvatar(): void
    {
        $userId = $this->models->newUserReturnId(photoUrl:'image.png');
        $this->assertStringEndsWith('/image.png', (string)User::query()->findOrFail($userId)->photo->url());
    }

    private function assertUserCan(bool $expected, int $userId, string $permission): void
    {
        /** @var User $user */
        $user = User::query()->findOrFail($userId);
        $this->assertSame($expected, $user->can($permission));
    }

    #[Test]
    public function newPostContent(): void
    {
        $this->models->newPost('content');
        $this->assertDatabaseHas('posts', ['text' => 'content']);
    }

    #[Test]
    public function newPostDeleted(): void
    {
        $this->models->newPostDeleted('content');
        $this->assertSoftDeleted('posts', ['text' => 'content']);
    }

    #[Test]
    public function newPostDeletedAt(): void
    {
        $this->models->newPostDeletedAt('content', '2005-04-02 21:37:00');
        $this->assertDatabaseHas('posts', [
            'text'       => 'content',
            'deleted_at' => '2005-04-02 21:37:00',
        ]);
    }

    #[Test]
    public function newPostCreatedAt(): void
    {
        $this->models->newPostCreatedAt('2005-04-02 21:37:00');
        $this->assertDatabaseHas('posts', [
            'created_at' => '2005-04-02 21:37:00+00',
        ]);
    }

    #[Test]
    public function newPostAuthorName(): void
    {
        $this->models->newPostAuthor('Mark');
        $this->assertDatabaseRelationHas('posts', 'user', ['name' => 'Mark']);
    }

    #[Test]
    public function newPostAuthorPhoto(): void
    {
        $this->models->newPostAuthorPhoto('image.png');
        $this->assertDatabaseRelationHas('posts', 'user', ['photo' => 'image.png']);
    }

    #[Test]
    public function newPostAuthorId(): void
    {
        $id = $this->models->newPostReturnAuthorId('content');
        $this->assertDatabaseRelationHas('posts', 'user', ['user_id' => $id]);
    }

    #[Test]
    public function newPostAuthorIdContent(): void
    {
        $this->models->newPostReturnAuthorId('content');
        $this->assertDatabaseRelationHas('posts', 'user', ['text' => 'content']);
    }

    #[Test]
    public function newComment(): void
    {
        $this->models->newComment('content');
        $this->assertDatabaseHas('post_comments', ['text' => 'content']);
    }

    #[Test]
    public function newCommentReported(): void
    {
        $reportedComments = $this->reportedCommentsCount();
        $this->models->newCommentReported();
        $this->assertSame($reportedComments + 1, $this->reportedCommentsCount());
    }

    private function reportedCommentsCount(): int
    {
        return Post\Comment::query()->whereHas('flags')->count();
    }

    private function assertDatabaseRelationHas(string $table, string $relation, array $data): void
    {
        if ($table !== 'posts') {
            throw new \InvalidArgumentException();
        }
        $query = Post::query()->whereHas($relation, fn(Builder $query) => $query->where($data));
        $this->assertTrue($query->exists());
    }

    #[Test]
    public function newUserCreatedAt(): void
    {
        $this->models->newUser(createdAt:'2005-04-02 21:37:00');
        $this->assertDatabaseHas('users', [
            'created_at' => '2005-04-02 21:37:00',
        ]);
    }

    #[Test]
    public function newUserVisitedAt(): void
    {
        $this->models->newUserReturnId(visitedAt:'2005-04-02 21:37:00');
        $this->assertDatabaseHas('users', [
            'visited_at' => '2005-04-02 21:37:00',
        ]);
    }
}
