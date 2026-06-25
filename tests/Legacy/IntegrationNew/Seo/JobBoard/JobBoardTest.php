<?php
namespace Tests\Legacy\IntegrationNew\Seo\JobBoard;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Legacy\IntegrationNew\Seo;

class JobBoardTest extends TestCase {
    use Seo\Meta\Fixture\MetaCanonical;

    #[Test]
    public function test() {
        $this->assertCanonical('/Praca/Technologia/java', '/Praca');
    }

    #[Test]
    public function anyTag() {
        $this->assertCanonical('/Praca/Technologia/kotlin', '/Praca');
    }

    #[Test]
    public function pagination() {
        $this->assertCanonical('/Praca/Technologia/java?page=2', '/Praca?page=2');
    }
}
