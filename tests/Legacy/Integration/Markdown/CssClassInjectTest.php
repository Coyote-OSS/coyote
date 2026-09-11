<?php
namespace Tests\Legacy\Integration\Markdown;

use Coyote\Services\Parser\Factories\PostFactory;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Legacy\Integration\BaseFixture;
use Tests\Legacy\Integration\BaseFixture\Server\Laravel;
use Tests\Legacy\Integration\Seo;

class CssClassInjectTest extends TestCase
{
    use Laravel\Application;
    use BaseFixture\ClearedCache;

    #[Test]
    public function divClassCssInject(): void
    {
        $this->assertRenderPost('<div class="inject">', "<div>\n</div>");
    }

    #[Test]
    public function spanStyleInject(): void
    {
        $this->assertRenderPost('<span style="font-size:1.0em;">', "<span>\n</span>");
    }

    private function assertRenderPost(string $text, string $expected): void
    {
        $parser = new PostFactory($this->laravel->app);
        Assert::assertSame($expected, $parser->parse($text));
    }
}
