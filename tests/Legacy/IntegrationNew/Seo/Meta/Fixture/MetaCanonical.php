<?php
namespace Tests\Legacy\IntegrationNew\Seo\Meta\Fixture;

use PHPUnit\Framework\Assert;
use Tests\Legacy\IntegrationNew\BaseFixture\Server;
use Tests\Legacy\IntegrationNew\BaseFixture\View;
use Tests\Legacy\IntegrationNew\BaseFixture\View\ViewDom;

trait MetaCanonical
{
    use View\HtmlView;
    use Server\RelativeUri;

    function assertSelfCanonical(string $uri): void
    {
        $this->assertCanonical($uri, $uri);
    }

    function assertCanonical(string $uri, string $canonical): void
    {
        Assert::assertThat(
            $this->metaCanonical($uri),
            $this->relativeUri($canonical));
    }

    function assertSelfCanonicalAbsolute(string $url): void
    {
        Assert::assertSame($url, $this->metaCanonical($url));
    }

    function metaCanonical(string $uri): ?string
    {
        $dom = new ViewDom($this->htmlView($uri));
        foreach ($dom->elements(xPath:"/html/head/link[@rel='canonical']") as $canonical) {
            return $canonical->getAttribute('href');
        }
        return null;
    }
}
