<?php
namespace Tests\Legacy\Integration\BaseFixture\Server;

use Coyote\User;
use Illuminate\Testing\TestResponse;

class Server
{
    private string $hostname;
    public string $baseUrl;

    public function __construct(private Laravel\TestCase $laravel)
    {
        $this->hostname = '4programmers.local';
        $this->baseUrl = "https://$this->hostname";
    }

    public function get(string $uri): TestResponse
    {
        return $this->call('GET', $uri);
    }

    public function call(string $method, string $uri): TestResponse
    {
        return $this->laravel->call(
            method:$method,
            uri:$this->absoluteUrl($uri),
            server:['SCRIPT_FILENAME' => 'index.php'],
        );
    }

    public function absoluteUrl(string $uri): string
    {
        return new Url($this->hostname, $uri);
    }

    public function postAs(string $uri, array $body, User $user): TestResponse
    {
        $this->laravel->actingAs($user);
        return $this->post($uri, $body);
    }

    public function login(User $user): void
    {
        $this->laravel->actingAs($user);
    }

    public function loginById(int $userId): void
    {
        $this->laravel->actingAs(User::query()->findOrFail($userId));
    }

    public function post(string $uri, array $body): TestResponse
    {
        return $this->laravel->json(
            method:'POST',
            uri:new Url($this->hostname, $uri),
            data:$body);
    }
}
