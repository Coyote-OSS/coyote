<?php
namespace Tests\Legacy\IntegrationNew\Seo\Meta;

use PHPUnit\Framework\TestCase;
use Tests\Legacy\IntegrationNew\Seo;

class MetaCanonicalTest extends TestCase {
    use Seo\Meta\Fixture\MetaCanonical;
    use Seo\Meta\Fixture\Models;

    /**
     * @test
     */
    public function canonical() {
        $this->assertSelfCanonical('/');
    }

    /**
     * @test
     */
    public function categories() {
        $this->assertSelfCanonical('/Forum');
    }

    /**
     * @test
     */
    public function categoriesNoPage() {
        $this->assertCanonical('/Forum?page=2', '/Forum');
    }

    /**
     * @test
     */
    public function categoryPage() {
        $this->newCategory('Fruits');
        $this->assertSelfCanonical('/Forum/Fruits?page=2');
    }

    /**
     * @test
     */
    public function categoryIgnoreQueryParam() {
        $this->newCategory('Cars');
        $this->assertCanonical('/Forum/Cars?sort=id&order=asc', '/Forum/Cars');
    }

    /**
     * @test
     */
    public function categoryIgnoreQueryParamPreservePage() {
        $this->newCategory('Cars');
        $this->assertCanonical('/Forum/Cars?sort=id&page=2&order=asc', '/Forum/Cars?page=2');
    }

    /**
     * @test
     */
    public function topicPage() {
        [$category, $topic] = $this->newTopic();
        $this->assertSelfCanonical("/Forum/$category/$topic?page=2");
    }

    /**
     * @test
     */
    public function ignoreQueryParam() {
        $this->assertCanonical('/Forum?query=param', '/Forum');
    }

    /**
     * @test
     */
    public function https() {
        $this->assertSelfCanonicalAbsolute('https://4programmers.local/');
    }

    /**
     * @test
     */
    public function http() {
        $canonical = $this->metaCanonical('http://4programmers.local/');
        $this->assertSame('https://4programmers.local/', $canonical);
    }

    /**
     * @test
     */
    public function staticSubdomain() {
        $canonical = $this->metaCanonical('http://static.4programmers.net/');
        $this->assertSame('https://4programmers.net/', $canonical);
    }
}
