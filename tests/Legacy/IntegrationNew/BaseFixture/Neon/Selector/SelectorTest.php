<?php
namespace Tests\Legacy\IntegrationNew\BaseFixture\Neon\Selector;

use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\TestCase;
use Tests\Legacy\IntegrationNew\BaseFixture\Neon\View\ViewDom;

class SelectorTest extends TestCase
{
    /**
     * @test
     */
    public function immediateChild(): void
    {
        $content = $this->find(
            new Selector('html', 'body', 'div', 'text()'),
            '<div>Foo</div>');
        $this->assertSame(['Foo'], $content);
    }

    /**
     * @test
     */
    public function nestedChild(): void
    {
        $content = $this->find(
            new Selector('html', 'body', 'div', 'text()'),
            '<div><div>Bar</div></div>');
        $this->assertSame(['Bar'], $content);
    }

    /**
     * @test
     */
    public function leafText(): void
    {
        $content = $this->find(
            new Selector('div', 'text()'),
            '<div>Banana<span>Watermelon</span></div>');
        $this->assertSame(['Banana'], $content);
    }

    /**
     * @test
     */
    public function leafAttribute(): void
    {
        $content = $this->find(
            new Selector('div', '@id'),
            '<div id="apple"><span id="pear"></span></div>');
        $this->assertSame(['apple'], $content);
    }

    /**
     * @test
     */
    public function nonLeafElement(): void
    {
        $this->assertTrue($this->exists(
            new Selector('div', 'img'),
            '<div><span><img/></span></div>'));
    }

    /**
     * @test
     */
    public function blank(): void
    {
        $exception = $this->caught(fn() => new Selector('html', ' ', 'body', 'text()'));
        $this->assertSame(
            'Failed to accept empty string selector.',
            $exception->getMessage());
    }

    private function caught(callable $block): \Throwable
    {
        try {
            $block();
        } catch (\Throwable $throwable) {
            return $throwable;
        }
        throw new AssertionFailedError('Failed to assert that exception is thrown.');
    }

    /**
     * @test
     */
    public function cssId(): void
    {
        $element = $this->find(
            new Selector('#foo', 'text()'),
            '<div id="foo">Bar</div>');
        $this->assertSame(['Bar'], $element);
    }

    /**
     * @test
     */
    public function cssIdNotFound(): void
    {
        $this->assertSame(
            [],
            $this->find(new Selector('#foo'), '<div>other</div>'));
    }

    /**
     * @test
     */
    public function cssIdInvalidId(): void
    {
        $this->assertSame(
            [],
            $this->find(new Selector('#foo'), '<div id="other">other</div>'));
    }

    /**
     * @test
     */
    public function cssClass(): void
    {
        $content = $this->find(
            new Selector('html', 'body', '.foo', 'text()'),
            '<div class="foo">Valar</div>
            <p class="foo">Morghulis</p>');
        $this->assertSame(['Valar', 'Morghulis'], $content);
    }

    /**
     * @test
     */
    public function cssElementClass(): void
    {
        $content = $this->find(
            new Selector('html', 'body', 'div.foo', 'text()'),
            '<div class="foo">Match</div>
            <p class="foo">Other</p>');
        $this->assertSame(['Match'], $content);
    }

    /**
     * @test
     */
    public function cssClassStandalone(): void
    {
        $fooClass = new Css('.foo');
        $dom = new ViewDom('<div class="bar">Green</div><div class="foo">Blue</div>');
        $this->assertSame(['Blue'],
            $dom->findStrings("/html/body/$fooClass/text()"));
    }

    /**
     * @test
     */
    public function throwForMalformedCssSelector(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Css('foo');
    }

    /**
     * @test
     */
    public function cssClassMany(): void
    {
        $content = $this->find(
            new Selector('html', 'body', '.two', 'text()'),
            '<div class="one two three">Match</div>');
        $this->assertSame(['Match'], $content);
    }

    /**
     * @test
     */
    public function cssClassSubstringStart(): void
    {
        $this->assertNotFound(
            new Selector('html', 'body', '.car'),
            '<div class="carpet">Text</div>');
    }

    /**
     * @test
     */
    public function cssClassSubstringEnd(): void
    {
        $this->assertNotFound(
            new Selector('html', 'body', '.pet'),
            '<div class="carpet">Text</div>');
    }

    /**
     * @test
     */
    public function cssClassNewline(): void
    {
        $content = $this->find(
            new Selector('html', 'body', '.foo', 'text()'),
            "<div class='\nfoo\n'>Match</div>");
        $this->assertSame(['Match'], $content);
    }

    private function assertNotFound(Selector $selector, string $html): void
    {
        $this->assertSame([], $this->find($selector, $html));
    }

    private function find(Selector $selector, string $html): array
    {
        $dom = new ViewDom($html);
        return $dom->findStrings($selector->xPath());
    }

    private function exists(Selector $selector, string $html): bool
    {
        $dom = new ViewDom($html);
        return $dom->exists($selector->xPath());
    }
}
