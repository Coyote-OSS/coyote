<?php

namespace Tests\Feature\Listeners;

use Coyote\Events\MicroblogSaved;
use Coyote\Listeners\DispatchMicroblogNotifications;
use Coyote\Microblog;
use Coyote\Notifications\Microblog\CommentedNotification;
use Coyote\Notifications\Microblog\SubmittedNotification;
use Coyote\Notifications\Microblog\UserMentionedNotification;
use Coyote\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class DispatchMicroblogNotificationsTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        Notification::fake();
    }

    public function testScopeShouldNotIncludeBlockedUsers()
    {
        /** @var Microblog $microblog */
        $microblog = factory(Microblog::class)->create();
        $microblog->subscribers()->create(['user_id' => $microblog->user_id]);

        /** @var User $blocked */
        $blocked = factory(User::class)->create();
        $microblog->subscribers()->create(['user_id' => $blocked->id]);

        // ban user
        $microblog->user->relations()->create(['related_user_id' => $blocked->id, 'is_blocked' => true]);

        $subscribers = $microblog->subscribers()->excludeBlocked($blocked->id)->pluck('user_id');

        $this->assertEmpty($subscribers);

        $follower = factory(User::class)->create();
        $microblog->subscribers()->create(['user_id' => $follower->id]);

        $subscribers = $microblog->subscribers()->excludeBlocked($blocked->id)->pluck('user_id');

        $this->assertNotEmpty($subscribers);
        $this->assertTrue($subscribers->contains($follower->id));
    }

    public function testTriggerListenerOnlyIfTextWasChanged()
    {
        /** @var Microblog $microblog */
        $microblog = factory(Microblog::class)->make();

        $event = new MicroblogSaved($microblog);
        $listener = $this->app[DispatchMicroblogNotifications::class];

        $this->assertFalse($listener->handle($event));

        ////////////////////////////////////////

        $microblog->wasRecentlyCreated = true;

        $event = new MicroblogSaved($microblog);

        $this->assertTrue($listener->handle($event));

        ////////////////////////////////////////

        $event->wasContentChanged = true;

        $this->assertTrue($listener->handle($event));
    }

    public function testDispatchMentionNotification()
    {
        /** @var User $user */
        $user = factory(User::class)->create();

        /** @var Microblog $microblog */
        $microblog = factory(Microblog::class)->create(['text' => "Hello @{{$user->name}}"]);
        $microblog->wasRecentlyCreated = true;

        event(new MicroblogSaved($microblog));

        Notification::assertSentTo($user, UserMentionedNotification::class);
    }

    public function testDoNotDispatchMentionNotificationUserWasBlocked()
    {
        /** @var User $blocked */
        $blocked = factory(User::class)->create();

        /** @var User $user */
        $user = factory(User::class)->create();
        $user->relations()->create(['related_user_id' => $blocked->id, 'is_blocked' => true]);

        /** @var Microblog $microblog */
        $microblog = factory(Microblog::class)->create(['user_id' => $blocked->id, 'text' => "Hello @{$user->name}"]);
        $microblog->wasRecentlyCreated = true;

        event(new MicroblogSaved($microblog));

        Notification::assertNothingSent();
    }

    public function testDispatchNotificationToAllSubscribers()
    {
        /** @var Microblog $microblog */
        $microblog = factory(Microblog::class)->create();
        $microblog->subscribers()->create(['user_id' => $microblog->user_id]);

        $comment = factory(Microblog::class)->create(['parent_id' => $microblog->id]);
        $comment->wasRecentlyCreated = true;

        event(new MicroblogSaved($comment));

        Notification::assertSentTo($microblog->user, CommentedNotification::class);
    }

    public function testDispatchOnlyOneNotification()
    {
        /** @var Microblog $microblog */
        $microblog = factory(Microblog::class)->create();
        $microblog->subscribers()->create(['user_id' => $microblog->user_id]);

        $comment = factory(Microblog::class)->create(['parent_id' => $microblog->id, 'text' => "Hello @{$microblog->user->name}"]);
        $comment->wasRecentlyCreated = true;

        event(new MicroblogSaved($comment));

        Notification::assertSentTo($microblog->user, CommentedNotification::class);
        Notification::assertNotSentTo($microblog->user, UserMentionedNotification::class);
    }

    public function testDispatchNotificationToFollowers()
    {
        /** @var User $user */
        $user = factory(User::class)->create();
        $follower = factory(User::class)->create();

        $follower->relations()->create(['related_user_id' => $user->id]);

        $microblog = factory(Microblog::class)->create(['user_id' => $user->id]);

        event(new MicroblogSaved($microblog));

        Notification::assertSentTo($follower, SubmittedNotification::class);
    }
}
