<?php
namespace Coyote\Services\Parser\Factories;

use Coyote\Repositories\Contracts\PageRepositoryInterface;
use Coyote\Repositories\Contracts\UserRepositoryInterface;
use Coyote\Repositories\Contracts\WordRepositoryInterface;
use Coyote\Services\Parser\CompositeParser;
use Coyote\Services\Parser\Parsers\Censore;
use Coyote\Services\Parser\Parsers\Latex;
use Coyote\Services\Parser\Parsers\Markdown;
use Coyote\Services\Parser\Parsers\Prism;
use Coyote\Services\Parser\Parsers\Purifier;
use Coyote\Services\Parser\Parsers\Smilies;
use Coyote\Services\Parser\Parsers\UnicodeEmojiSvg;

class PostFactory extends AbstractFactory {
    public function parse(string $text): string {
        $parser = new CompositeParser();
        $text = $this->parseAndCache($text, function () use ($parser) {
            $parser->attach(new Markdown(
                $this->container[UserRepositoryInterface::class],
                $this->container[PageRepositoryInterface::class],
                request()->getHost()));
            $parser->attach(new Latex());
            $parser->attach(new Purifier(null, $this->videoAllowed(), $this->linkHostname()));
            $parser->attach(new Censore($this->container[WordRepositoryInterface::class]));
            $parser->attach(new Prism());
            $parser->attach(new UnicodeEmojiSvg());
            return $parser;
        });
        if ($this->smiliesAllowed()) {
            $parser->attach(new Smilies());
            $text = $parser->parse($text);
        }
        return $text;
    }
}
