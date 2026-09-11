<?php
namespace Tests\Legacy\Integration\Seo\Link\Fixture;

use Coyote\Services\Parser\Factories\PostFactory;
use PHPUnit\Framework\Assert;
use Tests\Legacy\Integration\BaseFixture;
use Tests\Legacy\Integration\BaseFixture\Server\Laravel;

trait Assertion
{
    use Laravel\Application;
    use BaseFixture\ClearedCache;

    function assertRenderPost(string $text, string $expected): void
    {
        $parser = new PostFactory($this->laravel->app);
        Assert::assertSame("$expected\n", $parser->parse($text));
    }
}
