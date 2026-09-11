<?php
namespace Tests\Legacy\Integration\Canonical;

use PHPUnit\Framework\TestCase;
use Tests\Legacy\Integration\Canonical;

class HostnameTest extends TestCase
{
    use Canonical\Fixture\Assertion;

    /**
     * @test
     */
    public function www()
    {
        $this->assertRedirectAbsolute(
            'https://www.4programmers.local/',
            'https://4programmers.local/');
    }

    private function assertRedirectAbsolute(string $requestUri, string $expectedRedirect): void
    {
        $response = $this->get($requestUri)->assertRedirect();
        $this->assertSame($expectedRedirect, $response->headers->get('Location'));
        $this->assertStatusCode(301, $response->getStatusCode());
    }
}
