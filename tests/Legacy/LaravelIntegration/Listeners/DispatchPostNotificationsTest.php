<?php
namespace Tests\Legacy\LaravelIntegration\Listeners;

use Coyote\Events\PostSaved;
use Coyote\Forum;
use Coyote\Notifications\Post\ChangedNotification;
use Coyote\Notifications\Post\SubmittedNotification;
use Coyote\Notifications\Post\UserMentionedNotification;
use Coyote\Post;
use Coyote\Services\Notification\DatabaseChannel;
use Coyote\Services\Parser\Factories\PostFactory;
use Coyote\Topic;
use Coyote\User;
use Illuminate\Container\Container;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use Tests\Legacy\LaravelIntegration\TestCase;
use NotificationChannels\WebPush\WebPushChannel;
use PHPUnit\Framework\Attributes\PreCondition;
use PHPUnit\Framework\Attributes\Test;

class DispatchPostNotificationsTest extends TestCase
{
    use DatabaseTransactions;

    private Forum $forum;
    private Topic $topic;

    #[PreCondition]
    public function initialize(): void
    {
        $this->forum = factory(Forum::class)->create();
        $this->topic = factory(Topic::class)->create(['forum_id' => $this->forum->id]);
        Notification::fake();
    }

    #[Test]
    public function notificationIsSent_toSubscribers()
    {
        $user = $this->newUser();
        $this->userSubscribesTopic($user, $this->topic);
        [$post] = $this->someoneWritesPost();

        event(new PostSaved($post));

        Notification::assertSentTo($user, function (SubmittedNotification $notification, array $channels): true {
            $this->assertContains(DatabaseChannel::class, $channels);
            $this->assertContains(WebPushChannel::class, $channels);
            $this->assertContains('mail', $channels);
            $this->assertContains('broadcast', $channels);
            return true;
        });
    }

    #[Test]
    public function notificationIsSent_notifierIsPostAuthor()
    {
        $subscriber = $this->newUser();
        $this->userSubscribesTopic($subscriber, $this->topic);
        [$post, $postAuthor] = $this->someoneWritesPost();

        event(new PostSaved($post));

        Notification::assertSentTo($subscriber,
            fn(SubmittedNotification $notification) => $notification->notifier->id === $postAuthor->id);
    }

    #[Test]
    public function whenEditing_notifierIsEditor()
    {
        [$post] = $this->someoneWritesPost();
        $subscriber = $this->newUser();
        $this->userSubscribesPost($subscriber, $post);
        $editor = $this->newUser();
        $this->userEditsPost($editor, $post);

        event(new PostSaved($post));

        Notification::assertSentTo($subscriber,
            fn(ChangedNotification $notification) => $notification->notifier->id === $editor->id);
    }

    #[Test]
    public function notificationIsNotSent_whenUserAddsOwnPost()
    {
        $user = $this->newUser();
        $this->userSubscribesTopic($user, $this->topic);
        $post = $this->userAddsPost($user);

        event(new PostSaved($post));

        Notification::assertNothingSent();
    }

    #[Test]
    public function blockedUserAddingPost_doesNotNotifySubscriber()
    {
        $subscriber = $this->newUser();
        $blockedAuthor = $this->newUser();
        $this->userBlocksUser($subscriber, $blockedAuthor);

        $this->userSubscribesTopic($subscriber, $this->topic);
        $post = $this->userAddsPost($blockedAuthor);

        event(new PostSaved($post));

        Notification::assertNothingSent();
    }

    #[Test]
    public function mentioningUsernameInPost_sendsNotificationToMentionedUser()
    {
        $authorUser = $this->newUser(canMentionUsers:true);
        $mentionedUser = $this->newUser();
        $post = $this->userAddsPostMention($authorUser, $mentionedUser);

        event(new PostSaved($post));

        Notification::assertSentTo($mentionedUser, UserMentionedNotification::class);
    }

    #[Test]
    public function mentioningSelf_doesNotSendNotification()
    {
        $user = $this->newUser();
        $post = $this->userAddsPostMention($user, $user);

        event(new PostSaved($post));

        Notification::assertNothingSent();
    }

    #[Test]
    public function mentionedByBlockedUser_doesNotSendNotification_toMentionedUser()
    {
        $mentionedUser = $this->newUser();
        $blockedAuthor = $this->newUser();
        $this->userBlocksUser($mentionedUser, $blockedAuthor);
        $post = $this->userAddsPostMention($blockedAuthor, $mentionedUser);

        event(new PostSaved($post));

        Notification::assertNothingSent();
    }

    #[Test]
    public function mentionByUserWithoutSufficientReputation_doesNotSendNotification()
    {
        $post = $this->userAddsPostMention(
            $this->newUser(canMentionUsers:false),
            $this->newUser());

        event(new PostSaved($post));

        Notification::assertNothingSent();
    }

    #[Test]
    public function postAddedByFollowedAuthor_sendsNotification_toTheFollower()
    {
        $follower = $this->newUser();
        $author = $this->newUser();
        $this->userFollowsUser($follower, $author);
        $post = $this->userAddsPost($author);

        event(new PostSaved($post));

        Notification::assertSentTo($follower, SubmittedNotification::class);
    }

    #[Test]
    public function userBeingSubscribedAndFollowing_doesNotReceive_twoNotifications_forOnePost()
    {
        $follower = $this->newUser();
        $this->userSubscribesTopic($follower, $this->topic);

        $author = $this->newUser();
        $this->userFollowsUser($follower, $author);
        $post = $this->userAddsPost($author);

        event(new PostSaved($post));

        Notification::assertSentToTimes($follower, SubmittedNotification::class, 1);
    }

    #[Test]
    public function subscriberIsNotified_ofPostEdit()
    {
        [$post, $postAuthor] = $this->someoneWritesPost();
        $subscriber = $this->newUser();
        $this->userSubscribesPost($subscriber, $post);
        $this->userEditsPost($postAuthor, $post);

        event(new PostSaved($post));

        Notification::assertSentTo($subscriber, ChangedNotification::class);
    }

    #[Test]
    public function subscriberIsNotNotified_ofPostEdit_byBlockedUser()
    {
        [$post, $postAuthor] = $this->someoneWritesPost();
        $subscriber = $this->newUser();
        $this->userSubscribesPost($subscriber, $post);
        $this->userBlocksUser($subscriber, $postAuthor);
        $this->userEditsPost($postAuthor, $post);

        event(new PostSaved($post));

        Notification::assertNothingSent();
    }

    #[Test]
    public function notificationSender_isPostAuthor(): void
    {
        $user = $this->newUser();
        $this->userSubscribesTopic($user, $this->topic);
        $post = $this->userAddsPost($this->newUser(username:'Mark'));

        event(new PostSaved($post));

        Notification::assertSentTo($user,
            fn(SubmittedNotification $notification): bool => \in_array('Mark', $notification->sender()));
    }

    #[Test]
    public function notificationOfCreation_isNotSent_toDeletedSubscriber(): void
    {
        $goner = $this->newUser();
        $this->userSubscribesTopic($goner, $this->topic);
        $this->userDeletesAccount($goner);
        [$post] = $this->someoneWritesPost();

        event(new PostSaved($post));

        Notification::assertNothingSent();
    }

    #[Test]
    public function notificationOfEdit_isNotSent_toDeletedSubscriber(): void
    {
        [$post, $postAuthor] = $this->someoneWritesPost();
        $goner = $this->newUser();
        $this->userSubscribesPost($goner, $post);
        $this->userDeletesAccount($goner);
        $this->userEditsPost($postAuthor, $post);

        event(new PostSaved($post));

        Notification::assertNothingSent();
    }

    #[Test]
    public function editingLegacyPostWithoutUser_doesNotCrash(): void
    {
        $mentioned = $this->newUser();
        $post = $this->postIsAddedWithoutUserMentions($mentioned);
        $this->userEditsPost($this->newUser(), $post);
        event(new PostSaved($post));
        Notification::assertNothingSent();
    }

    #[Test]
    public function userBeingSubscribedAndMentioned_doesNotReceive_twoNotifications_forOnePost()
    {
        $subscriber = $this->newUser();
        $this->userSubscribesTopic($subscriber, $this->topic);
        $post = $this->userAddsPostMention($this->newUser(canMentionUsers:true), $subscriber);

        event(new PostSaved($post));

        Notification::assertSentToTimes($subscriber, UserMentionedNotification::class, 1); // If the user is mentioned in newly created post,
        Notification::assertSentToTimes($subscriber, SubmittedNotification::class, 0); // we don't need to notify of the newly created post.
    }

    #[Test]
    public function nonDirtyEdit_doesNotCrash(): void
    {
        // When edit is made from http controller, non-dirty changes
        // don't actually set editor in post. Hence post can
        // be not recently created and still not have an editor.
        [$post, $postAuthor] = $this->someoneWritesPost();
        $this
            ->actingAs($postAuthor)
            ->post($this->postSaveUrl($post), ['text' => $post->text])
            ->assertSuccessful();
    }

    #[Test]
    public function userWhoWasPreviouslyMentioned_isNotMentionedAgainInEdit(): void
    {
        $author = $this->newUser(canMentionUsers:true);

        $previouslyMentioned = $this->newUser('George');
        $post = $this->userAddsPost($author, '@George');
        $previous = $post->html;

        $newlyMentioned = $this->newUser('Michael');
        $this->userEditsPost($author, $post, '@George @Michael');

        event(new PostSaved($post, previousPostHtml:$previous));

        Notification::assertSentToTimes($previouslyMentioned, UserMentionedNotification::class, 0);
        Notification::assertSentToTimes($newlyMentioned, UserMentionedNotification::class, 1);
    }

    #[Test]
    public function updatingPostViaHttp_passesPreviousMarkdownPost_soUserIsNotMentionedAgain(): void
    {
        $this->newUser('Mark');
        $user = $this->newUser(canMentionUsers:true);
        $post = $this->userAddsPost($user, 'previous content @Mark');
        $this->clearPostMarkdownCache('updated content @Mark');

        $this
            ->actingAs($user)
            ->post($this->postSaveUrl($post), ['text' => 'updated content @Mark'])
            ->assertSuccessful();

        Notification::assertNothingSent();
    }

    #[Test]
    public function sendsNotificationToTreeTopicPostAuthor(): void
    {
        $treeTopic = $this->newTreeTopic();
        [$post, $postAuthor] = $this->someoneWritesPost($treeTopic);
        [$response] = $this->someoneWritesPost($treeTopic, treeResponseTo:$post);

        event(new PostSaved($response));

        Notification::assertSentToTimes($postAuthor, SubmittedNotification::class, 1);
    }

    #[Test]
    public function dontReceiveNotification_byRespondingToOwnPost(): void
    {
        $treeTopic = $this->newTreeTopic();
        [$post, $postAuthor] = $this->someoneWritesPost($treeTopic);
        [$response] = $this->someoneWritesPost($treeTopic, author:$postAuthor, treeResponseTo:$post);

        event(new PostSaved($response));

        Notification::assertSentToTimes($postAuthor, SubmittedNotification::class, 0);
    }

    private function newUser(?string $username = null, ?bool $canMentionUsers = null): User
    {
        $factoryBuilder = factory(User::class);
        if ($canMentionUsers) {
            $factoryBuilder->state('canMentionUsers');
        }
        $attributes = [];
        if ($username) {
            $attributes['name'] = $username;
        }
        return $factoryBuilder->create($attributes);
    }

    private function userSubscribesTopic(User $user, Topic $topic): void
    {
        $topic->subscribers()->create(['user_id' => $user->id]);
    }

    private function userSubscribesPost(User $user, Post $post): void
    {
        $post->subscribers()->create(['user_id' => $user->id]);
    }

    private function userFollowsUser(User $user, User $followee): void
    {
        $user->relations()->create(['related_user_id' => $followee->id, 'is_blocked' => false]);
    }

    private function userBlocksUser(User $user, User $blocked): void
    {
        $user->relations()->create(['related_user_id' => $blocked->id, 'is_blocked' => true]);
    }

    private function userAddsPost(User $user, ?string $postMarkdown = null): Post
    {
        $attributes = [
            'user_id'  => $user->id,
            'forum_id' => $this->forum->id,
            'topic_id' => $this->topic->id,
        ];
        if ($postMarkdown) {
            $this->clearPostMarkdownCache($postMarkdown);
            $attributes['text'] = $postMarkdown;
        }
        return factory(Post::class)->create($attributes);
    }

    private function someoneWritesPost(
        ?Topic $topic = null,
        ?Post  $treeResponseTo = null,
        ?User  $author = null,
    ): array
    {
        /** @var Post $post */
        $post = factory(Post::class)->create([
            'topic_id'            => $topic?->id ?? $this->topic->id,
            'forum_id'            => $this->forum->id,
            'user_id'             => $author?->id ?? factory(\Coyote\User::class),
            'tree_parent_post_id' => $treeResponseTo?->id,
        ]);
        return [$post, $post->user];
    }

    private function userAddsPostMention(User $authorUser, User $mentionedUser): Post
    {
        return $this->userAddsPost($authorUser, $this->mentionMarkdown($mentionedUser));
    }

    private function postIsAddedWithoutUserMentions(User $mentioned): Post
    {
        return factory(Post::class)
            ->state('legacyPostWithoutUser')
            ->create([
                'forum_id' => $this->forum->id,
                'topic_id' => $this->topic->id,
                'text'     => $this->mentionMarkdown($mentioned),
            ]);
    }

    private function userEditsPost(User $editor, Post $post, ?string $postMarkdown = null): void
    {
        $post->editor_id = $editor->id;
        $post->wasRecentlyCreated = false;
        if ($postMarkdown !== null) {
            $this->clearPostMarkdownCache($postMarkdown);
            $post->text = $postMarkdown;
        }
        $post->save();
    }

    private function userDeletesAccount(User $user): void
    {
        $user->delete();
    }

    private function mentionMarkdown(User $mentionedUser): string
    {
        return "Hello, @{{$mentionedUser->name}}!";
    }

    private function postSaveUrl(Post $post): string
    {
        return route('forum.topic.save', ['forum' => $this->forum, 'topic' => $this->topic, 'post' => $post]);
    }

    private function clearPostMarkdownCache(string $postMarkdown): void
    {
        $factory = new PostFactory(app(Container::class));
        $factory->cache->forget($factory->cache->key($postMarkdown));
    }

    private function newTreeTopic(): mixed
    {
        return factory(Topic::class)
            ->state('tree')
            ->create(['forum_id' => $this->forum->id]);
    }
}
