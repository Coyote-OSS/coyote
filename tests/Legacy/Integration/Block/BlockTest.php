<?php
namespace Tests\Legacy\Integration\Block;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Legacy\Integration\BaseFixture\Forum\ModelsDriver;
use Tests\Legacy\Integration\BaseFixture\Server;

class BlockTest extends TestCase
{
    use Server\Http;

    private ModelsDriver $models;

    #[Before]
    public function initializeModels(): void
    {
        $this->models = new ModelsDriver();
    }

    #[Test]
    public function test(): void
    {
        $userId = $this->loginUserAndReturnId();
        $response = $this->laravel->post("/User/Block/$userId");
        $this->assertSame(422, $response->status());
    }

    private function loginUserAndReturnId(): int
    {
        $userId = $this->models->newUserReturnId();
        $this->server->loginById($userId);
        return $userId;
    }
}
