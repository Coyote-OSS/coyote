<?php
namespace Tests\Legacy\Integration\Seo\Pastebin;

use PHPUnit\Framework\TestCase;
use Tests\Legacy\Integration\Seo;

class Test extends TestCase
{
    use Seo\Meta\Fixture\Assertion;

    public function test()
    {
        $this->assertNoIndexable('/Pastebin');
    }
}
