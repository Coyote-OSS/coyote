<?php
namespace Tests\Legacy\Integration\Canonical;

use PHPUnit\Framework\TestCase;
use Tests\Legacy\Integration\Canonical;

class Test extends TestCase
{
    use Canonical\Fixture\Assertion;

    /**
     * @test
     */
    public function emptyQueryParams()
    {
        $this->assertNoRedirectGet('/Forum?');
    }
}
