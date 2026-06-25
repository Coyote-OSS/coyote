<?php
namespace Tests\Legacy\IntegrationNew\Seo\Microblog;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Legacy\IntegrationNew\BaseFixture;
use Tests\Legacy\IntegrationNew\Seo;

class MicroblogTest extends TestCase {
    use BaseFixture\Forum\Models;
    use Seo\Microblog\Fixture\Assertion;
    use Seo\Meta\Fixture\MetaCanonical;
    use Seo\Meta\Fixture\Assertion;

    #[Test]
    public function paginationNoCanonical() {
        $this->assertCanonicalNotPresent('/Mikroblogi');
    }

    #[Test]
    public function microblogSelfCanonical() {
        $id = $this->driver->newMicroblogReturnId();
        $this->assertSelfCanonical("/Mikroblogi/View/$id");
    }

    #[Test]
    public function pagination() {
        $this->assertCrawlable('/Mikroblogi');
    }

    #[Test]
    public function microblogIndexable() {
        $id = $this->driver->newMicroblogReturnId();
        $this->assertIndexable("/Mikroblogi/View/$id");
    }
}
