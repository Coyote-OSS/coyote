<?php
namespace Tests\Legacy\IntegrationNew\BaseFixture\Neon\View;

use PHPUnit\Framework\TestCase;

class ViewDomFindTest extends TestCase
{
    /**
     * @test
     */
    public function find(): void
    {
        $dom = new ViewDom('<div><span></span></div>');
        $element = $dom->find('//div/span');
        $this->assertSame('span', $element->tagName());
    }

    /**
     * @test
     */
    public function missing(): void
    {
        // given
        $dom = new ViewDom('<div><span></span></div>');
        // then
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Failed to find element: //div/ul');
        // when
        $dom->find('//div/ul');
    }

    /**
     * @test
     */
    public function attribute(): void
    {
        $dom = new ViewDom('<div><span class="foo"></span></div>');
        $element = $dom->find('//div/span');
        $this->assertSame('foo', $element->attribute('class'));
    }

    /**
     * @test
     */
    public function attributeMissing(): void
    {
        $dom = new ViewDom('<div><span></span></div>');
        $element = $dom->find('//div/span');
        $this->assertNull($element->attribute('id'));
    }

    /**
     * @test
     */
    public function hasAttribute(): void
    {
        $dom = new ViewDom('<span class="foo"></span>');
        $element = $dom->find('//span');
        $this->assertTrue($element->hasAttribute('class'));
    }

    /**
     * @test
     */
    public function hasAttributeMissing(): void
    {
        $dom = new ViewDom('<span></span>');
        $element = $dom->find('*');
        $this->assertFalse($element->hasAttribute('class'));
    }

    /**
     * @test
     */
    public function child(): void
    {
        $dom = new ViewDom('<span><b></b><p class="foo"></p></span>');
        $element = $dom->find('//span');
        $this->assertSame('foo', $element->child('p')->attribute('class'));
    }

    /**
     * @test
     */
    public function childMissing(): void
    {
        // given
        $dom = new ViewDom('<span><b></b></span>');
        $element = $dom->find('//span');
        // then
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Failed to find child element: <i>');
        // when
        $element->child('i');
    }

    /**
     * @test
     */
    public function firstChildElement(): void
    {
        $dom = new ViewDom('<span>Foo<p class="foo"></p></span>');
        $element = $dom->find('//span');
        $this->assertSame('foo', $element->firstChild()->attribute('class'));
    }

    /**
     * @test
     */
    public function firstChildMissing(): void
    {
        // given
        $dom = new ViewDom('<span></span>');
        $element = $dom->find('//span');
        // then
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Failed to find child element.');
        // when
        $element->firstChild();
    }
}
