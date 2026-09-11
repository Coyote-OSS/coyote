<?php
namespace Tests\Legacy\Browser;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Legacy\Integration\BaseFixture;
use Tests\Legacy\Integration\BaseFixture\Server\Laravel;

/**
 * If nginx configuration changed, perform "nginx -s reload"
 * in nginx container, before running this test.
 */
class NginxTest extends TestCase
{
    use Laravel\Application;
    use BaseFixture\View\AssertsHtml;

    #[Test]
    public function staticFile(): void
    {
        $response = $this->get('http://nginx/robots.txt');
        $this->assertSame(200, $response->status());
    }

    #[Test]
    public function trailingPhpExtension(): void
    {
        $response = $this->get("http://nginx/Forum/Foo/bar.php");
        $this->assertSame(404, $response->status());
        $this->assertTextNodes(['404 :: 4programmers.net'], $response->body(), '/html/head/title/text()');
    }

    #[Test]
    public function indexPhp(): void
    {
        /**
         * This shouldn't even be a real case, but for some reason, index.php is
         * in public/ directory. Without exceptional case for this file, nginx
         * would serve this with try_files, which would serve index.php as
         * static file. We need exceptional case to make sure index.php is
         * executed, not served.
         * If index.php is moved outside of public/, then this case can be removed.
         */
        $response = $this->get('http://nginx/index.php');
        $this->assertSame(301, $response->status());
        $this->assertSame('http://nginx/', $response->header('Location'));
    }

    #[Test]
    public function redirectTrailingSlash(): void
    {
        $response = $this->get("http://nginx/Forum/");
        $this->assertSame(301, $response->status());
        $this->assertSame('http://nginx/Forum', $response->header('Location'));
    }

    #[Test]
    public function resourceHrefWithoutBasePath(): void
    {
        $response = $this->get("http://nginx/Forum/", allowRedirect:true); // slash at the end
        $stylesheetHref = $this->stylesheetHref($response);
        $this->assertStringStartsWith('/css/stylesEager-', $stylesheetHref);
        $this->assertStringStartsNotWith('/Forum/css/stylesEager-', $stylesheetHref);
    }

    private function stylesheetHref(Response $response): string
    {
        return $this->textNode($response->body(), '/html/head/link[@rel="stylesheet"]/@href');
    }

    private function get(string $uri, bool $allowRedirect = false): Response
    {
        return Http::withOptions(['allow_redirects' => $allowRedirect])->get($uri);
    }
}
