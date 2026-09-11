<?php

namespace Tests\Legacy\LaravelIntegration\Controllers\User;

use Coyote\Events\NotificationRead;
use Coyote\Notification;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Tests\Legacy\LaravelIntegration\TestCase;

class NotificationsControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function testShouldBroadcastNotificationReadEvent()
    {
        Event::fake();

        $notification = factory(Notification::class)->create();

        $response = $this->actingAs($notification->user)->get('notification/' . $notification->id);

        $response->assertStatus(302);
        $response->assertRedirect($notification->url);

        Event::assertDispatched(NotificationRead::class);
    }

    public function testShouldNotBroadcastNotificationReadEvent()
    {
        Event::fake();

        $notification = factory(Notification::class)->create(['read_at' => now()]); // notification seen before

        $response = $this->actingAs($notification->user)->get('notification/' . $notification->id);

        $response->assertStatus(302);
        $response->assertRedirect($notification->url);

        Event::assertNotDispatched(NotificationRead::class);
    }
}
