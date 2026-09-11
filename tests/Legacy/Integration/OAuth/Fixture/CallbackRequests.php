<?php
namespace Tests\Legacy\Integration\OAuth\Fixture;

use Coyote\Domain\OAuth\OAuth;
use Coyote\Domain\OAuth\User;
use Illuminate\Testing\TestResponse;
use Tests\Legacy\Integration\BaseFixture\Server;
use Tests\Legacy\Integration\BaseFixture\Server\Laravel;

trait CallbackRequests
{
    use Laravel\Application;
    use Server\Http;

    function oAuthCallback(array $query): TestResponse
    {
        return $this->server->get('/OAuth/google/Callback?' . \http_build_query($query));
    }

    function oAuthCallbackLogged(): TestResponse
    {
        return $this->oAuthLogged('google', 'irrelevant', 'irrelevant', 'irrelevant');
    }

    function oAuthCallbackUsername(string $oAuthUsername): TestResponse
    {
        return $this->oAuthLogged('google', 'irrelevant', $oAuthUsername, 'irrelevant');
    }

    function oAuthLoggedIn(string $email, ?string $username = null, ?string $provider = null, ?string $providerId = null): void
    {
        $this->oAuthLogged(
            $provider ?? 'google',
            $email,
            $username ?? 'irrelevant',
            $providerId ?? 'irrelevant');
    }

    private function oAuthLogged(string $provider, string $email, string $username, string $providerId): TestResponse
    {
        $this->oAuthUser(new User($providerId, $email, $username, null));
        return $this->server->get("/OAuth/$provider/Callback?" . \http_build_query(['code' => 'success']));
    }

    function oAuthUser(User $user): void
    {
        $this->laravel->app->instance(OAuth::class, new ConstantOAuth($user));
    }
}
