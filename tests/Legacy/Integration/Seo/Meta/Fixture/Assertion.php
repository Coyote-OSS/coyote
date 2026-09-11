<?php
namespace Tests\Legacy\Integration\Seo\Meta\Fixture;

use PHPUnit\Framework\Assert;
use Tests\Legacy\Integration\Seo;

trait Assertion
{
    use Seo\Meta\Fixture\MetaProperty;

    function assertIndexable(string $uri): void
    {
        $this->assertMetaRobots($uri, 'index,follow');
    }

    function assertNoIndexable(string $uri): void
    {
        $this->assertMetaRobots($uri, 'noindex,nofollow');
    }

    function assertCrawlable(string $uri): void
    {
        $this->assertMetaRobots($uri, 'noindex,follow');
    }

    function assertMetaRobots(string $uri, string $metaRobots): void
    {
        Assert::assertSame($metaRobots, $this->metaProperty('robots', $uri));
    }
}
