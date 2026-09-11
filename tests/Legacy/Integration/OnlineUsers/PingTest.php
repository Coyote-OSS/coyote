<?php
namespace Tests\Legacy\Integration\OnlineUsers;

use Coyote\Repositories\Redis\SessionRepository;
use Coyote\Services\Session\Handler;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Legacy\Integration\BaseFixture;

class PingTest extends TestCase
{
    use BaseFixture\Server\Http;

    #[Test]
    public function pingIsSavedAsReferer(): void
    {
        $this->request('/ping', 'http://localhost:8880/Mikroblogi');
        $this->assertSame('/Mikroblogi', $this->sessionPath());
    }

    #[Test]
    public function regularPageIsSavedAsItself(): void
    {
        $this->request('/Mikroblogi', 'http://localhost:8880/Other');
        $this->assertSame('/Mikroblogi', $this->sessionPath());
    }

    #[Test]
    public function ajaxSettingsIsSavedAsReferer(): void
    {
        $this->request('/User/Settings/Ajax', 'http://localhost:8880/Mikroblogi');
        $this->assertSame('/Mikroblogi', $this->sessionPath());
    }

    #[Test]
    public function searchIsSavedAsReferer(): void
    {
        $this->request('/Search?q=foo', 'http://localhost:8880/Mikroblogi');
        $this->assertSame('/Mikroblogi', $this->sessionPath());
    }

    #[Test]
    public function ignoreEmptyHttpRefererPathEmpty(): void
    {
        $this->request('/ping', 'http://localhost:8880/');
        $this->assertSame('/', $this->sessionPath());
    }

    #[Test]
    public function ignoreEmptyHttpRefererPathMissing(): void
    {
        $this->request('/ping', 'http://localhost:8880');
        $this->assertSame('/ping', $this->sessionPath());
    }

    #[Test]
    public function ignoreEmptyHttpRefererMissing(): void
    {
        $this->request('/ping', '');
        $this->assertSame('/ping', $this->sessionPath());
    }

    private function sessionPath(): string
    {
        /** @var Handler $handler */
        $handler = $this->laravel->app->get(Handler::class);
        /** @var SessionRepository $session */
        $session = $this->laravel->app->get(SessionRepository::class);
        $session->destroy(sessionId:1);
        $handler->write(1, \serialize([]));
        return \unserialize($session->get(sessionId:1))['path'];
    }

    private function request(string $uri, string $httpReferer): void
    {
        $this->server->get($uri);
        $this->spoofRequestHeader('Referer', $httpReferer);
    }

    private function spoofRequestHeader(string $headerName, string $value): void
    {
        /** @var Request $request */
        $request = $this->laravel->app->get(Request::class);
        $request->headers->set($headerName, $value);
    }
}
