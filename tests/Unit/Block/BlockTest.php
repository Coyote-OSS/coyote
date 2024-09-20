<?php
namespace Tests\Unit\Block;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Unit\BaseFixture\Forum\ModelsDsl;
use Tests\Unit\BaseFixture\Server;

class BlockTest extends TestCase
{
    use Server\Http;

    private ModelsDsl $models;

    #[Before]
    public function initializeModels(): void
    {
        $this->models = new ModelsDsl();
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
