<?php
namespace Tests\Legacy\Integration\Canonical\TrailingSlash;

use PHPUnit\Framework\TestCase;
use Tests\Legacy\Integration\Canonical;

class Test extends TestCase
{
    use Canonical\Fixture\Assertion;

    /**
     * @test
     */
    public function forumCategories()
    {
        $this->assertRedirectGet('/Forum/Categories/', '/Forum/Categories');
    }

    /**
     * @test
     */
    public function job()
    {
        $this->assertRedirectGet('/Praca/', '/Praca');
    }

    /**
     * @test
     */
    public function microblog()
    {
        $this->assertRedirectGet('/Mikroblogi/', '/Mikroblogi');
    }

    /**
     * @test
     */
    public function homepage()
    {
        $this->assertNoRedirectGet('/');
    }

    /**
     * @test
     */
    public function homepageQueryParam()
    {
        $this->assertNoRedirectGet('/?query=param');
    }

    /**
     * @test
     */
    public function queryParam()
    {
        $this->assertRedirectGet('/Forum/?query=param', '/Forum?query=param');
    }
}
