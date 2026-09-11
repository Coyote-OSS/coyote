<?php
namespace Tests\Legacy\Integration\Notification;

use PHPUnit\Framework\TestCase;
use Tests\Legacy\Integration\BaseFixture;

class Test extends TestCase
{
    use BaseFixture\Server\Http;

    /**
     * @test
     */
    public function nonUuidNotification(): void
    {
        $this->server->get('/notification/123')
            ->assertStatus(400);
    }

    /**
     * @test
     */
    public function missing(): void
    {
        $missing = '0000383b-d679-4635-aac0-2a658b8feff9';
        $this->server->get("/notification/$missing")
            ->assertStatus(404);
    }
}
