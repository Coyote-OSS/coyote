<?php
namespace Tests\Legacy\Services\Parser\Parsers;

use Coyote\Repositories\Eloquent\WordRepository;
use Coyote\Services\Parser\Parsers\Censore;

class CensoreTest extends \Tests\Legacy\TestCase
{
    protected Censore $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new Censore(new WordRepository(app()));
    }

    /**
     * @test
     */
    public function testHashCodeTag()
    {
        $this->assertIdentity('<code></code><code>kurczak');
        $this->assertIdentity('<code><code><code></code><code>kurczak');
        $this->assertIdentity('</code></code><code>kurczak');
    }

    private function assertIdentity(string $content): void
    {
        $this->assertSame(
            $this->parser->parse($content),
            $content);
    }
}
