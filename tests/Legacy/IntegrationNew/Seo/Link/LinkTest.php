<?php
namespace Tests\Legacy\IntegrationNew\Seo\Link;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Legacy\IntegrationNew\Seo;

class LinkTest extends TestCase {
    use Seo\Link\Fixture\Assertion;

    #[Test]
    public function markdownLink() {
        $this->assertRenderPost(
            '[foo](http://external)',
            '<p><a href="http://external" rel="nofollow">foo</a></p>');
    }

    #[Test]
    public function htmlLink() {
        $this->assertRenderPost(
            '<a href="http://external">foo</a>',
            '<p><a href="http://external" rel="nofollow">foo</a></p>');
    }

    #[Test]
    public function override() {
        $this->assertRenderPost(
            '<a href="http://external" rel="follow">foo</a>',
            '<p><a href="http://external" rel="nofollow">foo</a></p>');
    }
}
