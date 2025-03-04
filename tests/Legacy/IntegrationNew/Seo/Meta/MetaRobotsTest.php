<?php
namespace Tests\Legacy\IntegrationNew\Seo\Meta;

use PHPUnit\Framework\TestCase;
use Tests\Legacy\IntegrationNew\BaseFixture;
use Tests\Legacy\IntegrationNew\Seo;

class MetaRobotsTest extends TestCase
{
    use BaseFixture\Server\Laravel\Application;
    use Seo\Meta\Fixture\Assertion;

    /**
     * @test
     */
    public function homepage()
    {
        $this->assertIndexable('/');
    }

    /**
     * @test
     */
    public function userTags()
    {
        $this->assertNoIndexable('/Forum/Interesting?query');
    }

    /**
     * @test
     */
    public function category()
    {
        $this->assertIndexable('/Forum');
    }

    /**
     * @test
     */
    public function developerEnvironment()
    {
        $this->assertNoIndexable('http://4programmers.dev/');
    }

    /**
     * @test
     */
    public function search()
    {
        $this->assertCrawlable('/Search?query');
    }
}
