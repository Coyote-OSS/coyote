<?php
namespace Coyote\Services\Parser\Factories;

use Coyote\Repositories\Contracts\WordRepositoryInterface;
use Coyote\Services\Parser\CompositeParser;
use Coyote\Services\Parser\Parsers\Censore;
use Coyote\Services\Parser\Parsers\Purifier;

class JobFactory extends AbstractFactory {
    public function parse(string $text): string {
        return $this->parseAndCache($text, function () {
            $parser = new CompositeParser();
            $parser->attach(new Purifier());
            $parser->attach(new Censore($this->container[WordRepositoryInterface::class]));
            return $parser;
        });
    }
}
