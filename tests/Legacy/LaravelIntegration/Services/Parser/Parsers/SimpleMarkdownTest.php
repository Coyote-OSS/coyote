<?php
namespace Tests\Legacy\LaravelIntegration\Services\Parser\Parsers;

use Coyote\Repositories\Eloquent\PageRepository;
use Coyote\Repositories\Eloquent\UserRepository;
use Coyote\Services\Parser\Parsers\SimpleMarkdown;
use Tests\Legacy\LaravelIntegration\TestCase;

class SimpleMarkdownTest extends TestCase
{
    public function testDoNotChangeLineBreaks()
    {
        $markdown = new SimpleMarkdown(
            $this->app[UserRepository::class],
            $this->app[PageRepository::class],
            'host',
            singleLine:true);
        $this->assertIdentity($markdown, "one\ntwo\nthree\n");
    }

    public function testAutolinkExtension()
    {
        $markdown = new SimpleMarkdown(
            $this->app[UserRepository::class],
            $this->app[PageRepository::class],
            'host',
            singleLine:false);
        $link = 'https://docs.djangoproject.com/en/2.0/#first-steps';
        $this->assertEquals("<a href=\"$link\">$link</a>\n", $markdown->parse($link));
    }

    private function assertIdentity(SimpleMarkdown $markdown, string $input): void
    {
        $this->assertEquals($input, $markdown->parse($input));
    }
}
