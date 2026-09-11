<?php
namespace Tests\Legacy\Integration\Canonical\Topic\Fixture\PostEdit;

use Coyote\User;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Assert;
use Tests\Legacy\Integration\BaseFixture;

trait Assertion
{
    use BaseFixture\Server\RelativeUri;

    function assertRedirectHeadStatus(string $uri, int $status): void
    {
        $this->server->call('HEAD', $uri)
            ->assertRedirect()
            ->assertStatus($status);
    }

    function assertRedirectPostStatus(string $uri, int $status): void
    {
        $this->server->call('POST', $uri)
            ->assertRedirect()
            ->assertStatus($status);
    }

    function assertCanonicalPostEdit(User $author, string $uri): void
    {
        $this->request($uri, $author)
            ->assertSuccessful()
            ->assertStatus(200);
    }

    function assertRedirectPostEdit(User $author, string $uri, string $expectedRedirect): void
    {
        $response = $this->request($uri, $author)->assertRedirect();
        Assert::assertThat(
            $response->headers->get('Location'),
            $this->relativeUri($expectedRedirect));
    }

    function request(string $uri, User $author): TestResponse
    {
        return $this->server->postAs($uri,
            body:['title' => 'irrelevant irrelevant irrelevant', 'text' => 'irrelevant'],
            user:$author);
    }
}
