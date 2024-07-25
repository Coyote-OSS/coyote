<?php
namespace Tests\Unit\BaseFixture\Forum;

use Carbon\Carbon;
use Coyote\Flag;
use Coyote\Forum;
use Coyote\Group;
use Coyote\Microblog;
use Coyote\Permission;
use Coyote\Post;
use Coyote\Topic;
use Coyote\User;

class ModelsFactory
{
    public function newUserReturn(
        string $name = null,
        string $email = null,
        string $photoUrl = null,
        bool   $emailConfirmed = null,
        bool   $deleted = null,
    ): User
    {
        $user = new User();
        $user->name = $name ?? 'irrelevant' . \uniqId();
        $user->email = $email ?? 'irrelevant';
        $user->is_confirm = $emailConfirmed ?? false;
        $user->deleted_at = $deleted;
        $user->photo = $photoUrl;
        $user->save();
        return $user;
    }

    public function assignToGroupWithPermission(User $user, string $permissionName): void
    {
        $user->groups()->sync([$this->groupWithPermission($permissionName)->id]);
    }

    private function groupWithPermission(string $permissionName): Group
    {
        /** @var Permission $permission */
        $permission = Permission::query()->createOrFirst(
            ['name' => $permissionName],
            ['name' => $permissionName]);
        /** @var Group $group */
        $group = Group::query()->create(['name' => 'irrelevant']);
        $group->permissions()->updateExistingPivot($permission->id, ['value' => true]);
        return $group;
    }

    public function newPostReturn(
        string $content = null,
        Carbon $createdAt = null,
        string $deletedAt = null,
        string $authorName = null,
        string $authorPhotoUrl = null,
    ): Post
    {
        $forumId = $this->newForumReturnId();
        $post = new Post([
            'text'     => $content ?? 'irrelevant',
            'ip'       => 'irrelevant',
            'topic_id' => $this->newTopicReturnId($forumId),
            'forum_id' => $forumId,
            'user_id'  => $this->newUserReturn(name:$authorName, photoUrl:$authorPhotoUrl)->id,
        ]);
        if ($createdAt) {
            $post->created_at = $createdAt;
        }
        $post->deleted_at = $deletedAt;
        $post->save();
        return $post;
    }

    private function newForumReturnId(): int
    {
        /** @var Forum $forum */
        $forum = Forum::query()->create(['name' => 'irrelevant', 'slug' => 'irrelevant', 'description' => 'irrelevant']);
        return $forum->id;
    }

    private function newTopicReturnId(int $forumId): int
    {
        /** @var Topic $model */
        $model = Topic::query()->create(['title' => 'irrelevant', 'forum_id' => $forumId]);
        return $model->id;
    }

    public function newMicroblogReturn(
        string $content = null,
        string $deletedAt = null,
    ): Microblog
    {
        $microblog = new Microblog([
            'user_id' => $this->newUserReturn()->id,
            'text'    => $content ?? 'irrelevant',
        ]);
        $microblog->deleted_at = $deletedAt;
        $microblog->save();
        return $microblog;
    }

    public function reportPost(Post $post, ?string $reportContent): Flag
    {
        $flag = $this->newReport($reportContent);
        $flag->posts()->attach($post->id);
        $flag->save();
        return $flag;
    }

    public function reportMicroblog(Microblog $microblog, ?string $reportContent): Flag
    {
        $flag = $this->newReport($reportContent);
        $flag->microblogs()->attach($microblog->id);
        $flag->save();
        return $flag;
    }

    public function reportComment(Post\Comment $comment): Flag
    {
        $flag = $this->newReport(null);
        $flag->postComments()->attach($comment->id);
        $flag->save();
        return $flag;
    }

    private function newReport(?string $content): Flag
    {
        /** @var Flag $flag */
        $flag = Flag::query()->create([
            'type_id' => $this->flagTypeId(),
            'user_id' => $this->newUserReturn()->id,
            'url'     => '',
            'text'    => $content,
        ]);
        return $flag;
    }

    private function flagTypeId(): int
    {
        return Flag\Type::query()->first()->id;
    }

    public function newCommentReturn(string $content = null): Post\Comment
    {
        $comment = new Post\Comment();
        $comment->text = $content ?? 'irrelevant';
        $comment->post_id = $this->newPostReturn()->id;
        $comment->user_id = $this->newUserReturn()->id;
        $comment->save();
        return $comment;
    }
}
