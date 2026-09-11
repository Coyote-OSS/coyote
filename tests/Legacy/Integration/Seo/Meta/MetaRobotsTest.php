<?php
namespace Tests\Legacy\Integration\Seo\Meta;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Legacy\Integration\BaseFixture;
use Tests\Legacy\Integration\Seo;

class MetaRobotsTest extends TestCase {
    use BaseFixture\Server\Laravel\Application;
    use Seo\Meta\Fixture\Assertion;

    #[Test]
    public function homepage() {
        $this->assertIndexable('/');
    }

    #[Test]
    public function userTags() {
        $this->assertNoIndexable('/Forum/Interesting?query');
    }

    #[Test]
    public function category() {
        $this->assertIndexable('/Forum');
    }

    #[Test]
    public function developerEnvironment() {
        $this->assertNoIndexable('http://4programmers.dev/');
    }

    #[Test]
    public function search() {
        $this->assertCrawlable('/Search?query');
    }
}
