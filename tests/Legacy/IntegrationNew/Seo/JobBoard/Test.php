<?php
namespace Tests\Legacy\IntegrationNew\Seo\JobBoard;

use PHPUnit\Framework\TestCase;
use Tests\Legacy\IntegrationNew\Seo;

class Test extends TestCase
{
    use Seo\Meta\Fixture\MetaCanonical;

    public function test()
    {
        $this->assertCanonical('/Praca/Technologia/java', '/Praca');
    }

    /**
     * @test
     */
    public function anyTag()
    {
        $this->assertCanonical('/Praca/Technologia/kotlin', '/Praca');
    }

    /**
     * @test
     */
    public function pagination()
    {
        $this->assertCanonical('/Praca/Technologia/java?page=2', '/Praca?page=2');
    }
}
