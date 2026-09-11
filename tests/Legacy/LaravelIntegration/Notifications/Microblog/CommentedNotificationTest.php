<?php

namespace Tests\Legacy\LaravelIntegration\Notifications\Microblog;

use Coyote\Microblog;
use Coyote\Notifications\Microblog\CommentedNotification;
use Coyote\User;
use Faker\Factory;
use Tests\Legacy\LaravelIntegration\TestCase;

class CommentedNotificationTest extends TestCase
{
    public function testMergeNotification()
    {
        $sender = factory(User::class)->create();
        $recipient = factory(User::class)->create();

        $parent = Microblog::forceCreate(['user_id' => $sender->id, 'text' => Factory::create()->text]);

        $text1 = "Hello";
        $microblog = Microblog::forceCreate(['user_id' => $sender->id, 'text' => $text1, 'parent_id' => $parent->id]);

        $notification = new CommentedNotification($microblog);
        $recipient->notify($notification);

        $this->assertDatabaseHas('notifications', ['user_id' => $recipient->id, 'excerpt' => $text1]);

        // hit again
        $text2 = "Hello v2";
        $microblog = Microblog::forceCreate(['user_id' => $sender->id, 'text' => $text2, 'parent_id' => $parent->id]);

        $notification = new CommentedNotification($microblog);
        $recipient->notify($notification);

        $this->assertDatabaseMissing('notifications', ['user_id' => $recipient->id, 'excerpt' => $text2]);
    }
}
