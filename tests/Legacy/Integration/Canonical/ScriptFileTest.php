<?php
namespace Tests\Legacy\Integration\Canonical;

use PHPUnit\Framework\TestCase;
use Tests\Legacy\Integration\Canonical;

class ScriptFileTest extends TestCase
{
    use Canonical\Fixture\Assertion;

    /**
     * @test
     */
    public function test()
    {
        $this->assertRedirectGet('/index.php', '/');
    }

    /**
     * @test
     */
    public function trailingSlash()
    {
        $this->assertRedirectGet('/index.php/', '/');
    }

    /**
     * @test
     */
    public function forum()
    {
        $this->assertRedirectGet('/index.php/Forum', '/Forum');
    }

    /**
     * @test
     */
    public function forumTrailingSlash()
    {
        $this->assertRedirectGet('/index.php/Forum/', '/Forum');
    }
}
