<?php
namespace Coyote\Http\Middleware;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation;

class RedirectToCanonicalUrl
{
    public function handle(Request $request, callable $next): HttpFoundation\Response
    {
        if ($this->hasTrailingSlash($request)
            || $this->hasScriptFile($request)
            || $this->hasWww($request)) {
            return $this->redirectToCanonical($request);
        }
        return $next($request);
    }

    private function hasTrailingSlash(Request $request): bool
    {
        $uri = $request->getPathInfo();
        if ($uri === '/') {
            return false;
        }
        return \str_ends_with($uri, '/');
    }

    private function hasScriptFile(Request $request): bool
    {
        return $request->getBaseUrl() === '/index.php';
    }

    private function hasWww(Request $request): bool
    {
        return \str_starts_with($request->getHost(), 'www.');
    }

    private function hasFirstPage(Request $request): bool
    {
        $array = $this->queryParams($request);
        if (\array_key_exists('page', $array)) {
            return $array['page'] === '1';
        }
        return false;
    }

    private function redirectToCanonical(Request $request): RedirectResponse
    {
        return redirect()->away(
            $this->canonicalUrl($request),
            $this->statusCode($request));
    }

    private function canonicalUrl(Request $request): string
    {
        return $this->schemeAndHostname($request) .
            $this->canonicalUri($request) .
            $this->canonicalQueryString($request);
    }

    private function schemeAndHostname(Request $request): string
    {
        return \str_replace('www.', '', $request->getSchemeAndHttpHost());
    }

    private function canonicalUri(Request $request): string
    {
        $uri = $request->getPathInfo();
        if ($uri === '/') {
            return $uri;
        }
        if (\str_ends_with($uri, '/')) {
            return \subStr($uri, 0, -1);
        }
        return $uri;
    }

    private function canonicalQueryString(Request $request): string
    {
        $params = $this->queryParams($request);
        if ($this->hasFirstPage($request)) {
            unset($params['page']);
        }
        return \rTrim('?' . \http_build_query($params), '?');
    }

    private function statusCode(Request $request): int
    {
        if ($request->isMethod('GET')) {
            return 301;
        }
        return 308;
    }

    private function queryParams(Request $request): array
    {
        \parse_str($request->server->get('QUERY_STRING'), $queryParams);
        return $queryParams;
    }
}
