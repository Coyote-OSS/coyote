<?php

namespace Tests\Legacy\LaravelIntegration\Notifications;

use Coyote\Notification;
use Coyote\Notifications\PmCreatedNotification;
use Coyote\Pm;
use Coyote\User;
use Faker\Factory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Legacy\LaravelIntegration\TestCase;

class PmCreatedNotificationTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @var User
     */
    private $sender;

    /**
     * @var User
     */
    private $recipient;

    public function setUp(): void
    {
        parent::setUp();

        $this->sender = factory(User::class)->create();
        $this->recipient = factory(User::class)->create();

        $setting = $this->recipient->notificationSettings()->where('type_id', Notification::PM)->where('channel', Notification::DB)->first();
        $setting->is_enabled = true; // enable notification
        $setting->save();
    }

    public function testNotificationSuccessfullySent()
    {
        $text = Factory::create()->text(20);

        $repository = app(\Coyote\Repositories\Contracts\PmRepositoryInterface::class);
        $pm = $repository->submit($this->sender, ['author_id' => $this->recipient->id, 'text' => $text]);

        $notification = new PmCreatedNotification($pm[Pm::INBOX]);
        $this->recipient->notify($notification);

        $this->assertDatabaseHas('notifications', ['type_id' => Notification::PM, 'user_id' => $this->recipient->id, 'subject' => $text]);
    }
}
