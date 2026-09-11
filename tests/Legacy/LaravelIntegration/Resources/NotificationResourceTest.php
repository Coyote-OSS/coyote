<?php
namespace Tests\Legacy\LaravelIntegration\Resources;

use Carbon\Carbon;
use Coyote\Http\Resources\NotificationResource;
use Coyote\Notification;
use Coyote\Notification\Sender;
use Illuminate\Database\Eloquent\Collection;
use Tests\Legacy\LaravelIntegration\TestCase;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;

class NotificationResourceTest extends TestCase
{
    private Notification $notification;

    #[Before]
    public function initialize(): void
    {
        $this->notification = new Notification();
        $this->notification->forceFill([
            'created_at' => Carbon::now(),
            'headline'   => '{sender} dodał odpowiedź w wątku',
        ]);
    }

    #[Test]
    public function manyNotifiers(): void
    {
        $this->notificationSenders([
            new Sender(['name' => 'Mark']),
            new Sender(['name' => 'George']),
            new Sender(['name' => 'Steven']),
        ]);
        $this->assertStringContainsString('Mark (oraz 2 osoby)', $this->headline());
    }

    #[Test]
    public function twoNotifiers(): void
    {
        $this->notificationSenders([
            new Sender(['name' => 'Daniel']),
            new Sender(['name' => 'Peter']),
        ]);
        $this->assertStringContainsString('Daniel (oraz Peter)', $this->headline());
    }

    #[Test]
    public function singleNotifier()
    {
        $this->notificationSenders([
            new Sender(['name' => 'Joey']),
        ]);
        $this->assertStringContainsString('Joey', $this->headline());
    }

    private function headline(): string
    {
        $resource = new NotificationResource($this->notification);
        return $resource->toArray(app('request'))['headline'];
    }

    private function notificationSenders(array $senders): void
    {
        $this->notification->setRelation('senders', Collection::make($senders));
    }
}
