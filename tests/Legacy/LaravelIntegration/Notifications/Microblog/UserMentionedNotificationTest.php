<?php

namespace Tests\Legacy\LaravelIntegration\Notifications\Microblog;

use Coyote\Microblog;
use Coyote\Notification;
use Coyote\Notifications\Microblog\UserMentionedNotification;
use Coyote\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Tests\Legacy\LaravelIntegration\TestCase;

class UserMentionedNotificationTest extends TestCase
{
    use DatabaseTransactions;

    public function testNotificationSuccessfulSaved()
    {
        Mail::fake();

        $sender = factory(User::class)->create();
        $recipient = factory(User::class)->create();

        $text = "Hello @{$recipient->name}";
        /** @var Microblog $microblog */
        $microblog = Microblog::forceCreate(['user_id' => $sender->id, 'text' => $text]);

        $this->assertDatabaseHas('microblogs', ['user_id' => $sender->id, 'text' => $text]);

        $notification = new UserMentionedNotification($microblog);
        $recipient->notify($notification);

        $this->assertDatabaseHas('notifications', ['user_id' => $recipient->id, 'subject' => $text]);

        $notification = new UserMentionedNotification($microblog);
        $recipient->notify($notification);

        $notification = Notification::where(['user_id' => $recipient->id, 'subject' => $text])->get()->first();

        $this->assertEquals(2, $notification->senders->count());
    }
}
