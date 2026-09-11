<?php
namespace Tests\Legacy\Integration\Canonical\Fixture;

use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Assert;
use Tests\Legacy\Integration\BaseFixture\Server;

trait Assertion
{
    use Server\Http, Server\RelativeUri;

    function assertRedirectGet(string $requestUri, string $expectedRedirect): void
    {
        $this->assertRedirect($this->get($requestUri), $expectedRedirect, status:301);
    }

    function assertNoRedirectGet(string $requestUri): void
    {
        $this->assertNoRedirect($this->get($requestUri));
    }

    function assertNoRedirect(TestResponse $response): void
    {
        $response->assertStatus(200);
    }

    function assertRedirect(TestResponse $response, string $location, int $status): void
    {
        $response->assertRedirect();
        $this->assertRelativeUri($location, $response->headers->get('Location'));
        $this->assertStatusCode($status, $response->getStatusCode());
    }

    function head(string $requestUri): TestResponse
    {
        return $this->server->call('HEAD', $requestUri);
    }

    function get(string $requestUri): TestResponse
    {
        return $this->server->call('GET', $requestUri);
    }

    function post(string $requestUri): TestResponse
    {
        return $this->server->call('POST', $requestUri);
    }

    function assertRelativeUri(string $expected, string $actual): void
    {
        Assert::assertThat($actual, $this->relativeUri($expected),
            "Failed asserting redirect to: $expected (instead redirected to $actual).");
    }

    function assertStatusCode(int $expected, int $actual): void
    {
        Assert::assertSame($expected, $actual,
            "Failed asserting redirect returns status code [$expected] (actually was [$actual]).");
    }
}
