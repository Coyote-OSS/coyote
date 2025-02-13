<?php
namespace Tests\Legacy\IntegrationNew\Tags;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Legacy\IntegrationNew\BaseFixture\Server\Http;

class TagsTest extends TestCase
{
    use Http;

    #[Test]
    public function test(): void
    {
        $response = $this->server->post('/Forum/Tag/Validation', ['tags' => ['foo', null]]);
        $this->assertSame(422, $response->status());
    }
}
