<?php
namespace Tests\Legacy\Integration\BaseFixture\Server\Laravel;

use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Foundation\Testing\Concerns;
use Illuminate\Http\Request;
use Illuminate\Testing\TestResponse;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

trait OverridesSymfonyRequest
{
    use Concerns\MakesHttpRequests;

    protected abstract function mapSymfonyRequest(string $uri, SymfonyRequest $request): SymfonyRequest;

    /**
     * This method is a copy of {@see Concerns\MakesHttpRequests::call}, with the
     * exception of calling {@see OverridesSymfonyRequest::mapSymfonyRequest} before
     * passing it to {@see Request::createFromBase}.
     *
     * The reason for it is that symfony request manipulates the original URI. Because
     * of that, faking character-sensitive requests in testing is impossible. For example,
     * sending a request with a trailing `?` query separator - in order to test a 301
     * redirection for SEO, is impossible because of symfony default path parsing.
     *
     * If there is a better way to pass a trailing `?` query separator, or symfony
     * is updated, so the request factory doesn't automatically remove `?`, then this
     * class can be removed.
     */
    public function call(
        $method,
        $uri,
        $parameters = [],
        $cookies = [],
        $files = [],
        $server = [],
        $content = null): TestResponse
    {
        $kernel = $this->app->make(HttpKernel::class);
        $files = array_merge($files, $this->extractFilesFromDataArray($parameters));
        $symfonyRequest = SymfonyRequest::create(
            $this->prepareUrlForRequest($uri), $method, $parameters,
            $cookies, $files, array_replace($this->serverVariables, $server), $content,
        );
        $request = Request::createFromBase($this->mapSymfonyRequest($uri, $symfonyRequest));
        $response = $kernel->handle($request);
        $kernel->terminate($request, $response);
        if ($this->followRedirects) {
            $response = $this->followRedirects($response);
        }
        return $this->createTestResponse($response, $request);
    }
}
