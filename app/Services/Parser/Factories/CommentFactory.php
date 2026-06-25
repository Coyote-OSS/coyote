<?php
namespace Coyote\Services\Parser\Factories;

use Coyote\Repositories\Contracts\PageRepositoryInterface;
use Coyote\Repositories\Contracts\UserRepositoryInterface;
use Coyote\Repositories\Contracts\WordRepositoryInterface;
use Coyote\Services\Parser\CompositeParser;
use Coyote\Services\Parser\Parsers\Censore;
use Coyote\Services\Parser\Parsers\Emphasis;
use Coyote\Services\Parser\Parsers\Purifier;
use Coyote\Services\Parser\Parsers\SimpleMarkdown;
use Coyote\Services\Parser\Parsers\Smilies;
use Illuminate\Container\Container;

class CommentFactory extends AbstractFactory {
    public function __construct(
        Container            $container,
        private readonly int $userId,
    ) {
        parent::__construct($container);
    }

    public function parse(string $text): string {
        $this->cache->setId(class_basename($this) . $this->userId);

        $parser = new CompositeParser();

        $text = $this->parseAndCache($text, function () use ($parser) {
            $parser->attach(new SimpleMarkdown(
                $this->container[UserRepositoryInterface::class],
                $this->container[PageRepositoryInterface::class],
                request()->getHost(),
                singleLine:true));
            $parser->attach(new Purifier(
                ['b', 'strong', 'i', 'u', 'em', 'del', 'a[href|title|data-user-id|class]', 'code'],
                hostname:$this->linkHostname()));
            $parser->attach(new Censore($this->container[WordRepositoryInterface::class]));

            if (!empty($this->userId)) {
                $parser->attach(new Emphasis($this->userId, $this->container[UserRepositoryInterface::class]));
            }

            return $parser;
        });
        if ($this->smiliesAllowed()) {
            $parser->attach(new Smilies());
            $text = $parser->parse($text);
        }
        return $text;
    }
}
