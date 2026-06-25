<?php
namespace Coyote\Services\Parser\Factories;

use Coyote\Repositories\Contracts\PageRepositoryInterface;
use Coyote\Repositories\Contracts\UserRepositoryInterface;
use Coyote\Repositories\Contracts\WordRepositoryInterface;
use Coyote\Services\Parser\CompositeParser;
use Coyote\Services\Parser\Parsers\Censore;
use Coyote\Services\Parser\Parsers\Purifier;
use Coyote\Services\Parser\Parsers\SimpleMarkdown;
use Coyote\Services\Parser\Parsers\Smilies;

class SigFactory extends AbstractFactory {
    public function parse(string $text): string {
        $parser = new CompositeParser();
        $text = $this->parseAndCache($text, function () use ($parser): CompositeParser {
            $markdown = new SimpleMarkdown(
                $this->container[UserRepositoryInterface::class],
                $this->container[PageRepositoryInterface::class],
                request()->getHost(),
                singleLine:false);
            $parser->attach($markdown);
            $parser->attach(new Purifier(
                ['b', 'strong', 'i', 'em', 'del', 'a[href|title|data-user-id|class]', 'code', 'br'],
                hostname:$this->linkHostname()));
            $parser->attach(new Censore($this->container[WordRepositoryInterface::class]));
            return $parser;
        });
        if ($this->smiliesAllowed()) {
            $parser->attach(new Smilies());
            $text = $parser->parse($text);
        }
        return $text;
    }
}
