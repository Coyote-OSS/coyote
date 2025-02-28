<?php
namespace Tests\Legacy\IntegrationNew\BaseFixture\Neon\View;

use PHPUnit\Framework\TestCase;

class ViewDomFindElementsTest extends TestCase
{
    /**
     * @test
     */
    public function rejectTextNode(): void
    {
        $dom = new ViewDom('<div>Foo</div>');
        $this->expectException(\Exception::class);
        $dom->findElementsFlatTexts('/html/body/div/text()');
    }

    /**
     * @test
     */
    public function rejectAttribute(): void
    {
        $dom = new ViewDom('<div attr="value"></div>');
        $this->expectException(\Exception::class);
        $dom->findElementsFlatTexts('/html/body/div/@attr');
    }

    /**
     * @test
     */
    public function textNodeExceptionMessage(): void
    {
        $dom = new ViewDom('<div>Foo</div>');
        $this->expectExceptionMessage("Failed to get element as flat string: received a text node.");
        $dom->findElementsFlatTexts('/html/body/div/text()');
    }

    /**
     * @test
     */
    public function attributeExceptionMessage(): void
    {
        $dom = new ViewDom('<div attr>Foo</div>');
        $this->expectExceptionMessage("Failed to get element as flat string: received an attribute node.");
        $dom->findElementsFlatTexts('/html/body/div/@attr');
    }

    /**
     * @test
     */
    public function textContent(): void
    {
        $dom = new ViewDom('<div>Foo<strong>Bar</strong></div>');
        $this->assertSame(
            ['FooBar'],
            $dom->findElementsFlatTexts('/html/body/div'));
    }
}
