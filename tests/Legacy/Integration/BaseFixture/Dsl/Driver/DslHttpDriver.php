<?php
namespace Tests\Legacy\Integration\BaseFixture\Dsl\Driver;

use Illuminate\Testing\TestResponse;
use Tests\Legacy\Integration\BaseFixture\Dsl\Request\CreateTopic;
use Tests\Legacy\Integration\BaseFixture\Server\Server;

class DslHttpDriver
{
    private ?TestResponse $lastResponse = null;

    public function __construct(public Server $server) {}

    public function postCreateTopic(CreateTopic $request): void
    {
        $this->lastResponse = $this->server->post("/Forum/$request->categorySlug/Submit", [
            'title'       => $request->title,
            'text'        => 'content',
            'discussMode' => $request->discussMode,
        ]);
    }

    public function assertResponseSuccess(): void
    {
        $this->lastResponse->assertSuccessful();
    }

    public function assertResponseForbidden(): void
    {
        $this->lastResponse->assertForbidden();
    }

    public function assertResponseUnauthenticated(): void
    {
        $this->lastResponse->assertUnauthorized();
    }

    public function lastResponseJsonField(string $jsonField): string|int|null
    {
        if ($this->lastResponse->isSuccessful()) {
            return $this->lastResponse->json($jsonField);
        }
        return null;
    }

    public function lastResponseStatus(): int
    {
        return $this->lastResponse->status();
    }
}
