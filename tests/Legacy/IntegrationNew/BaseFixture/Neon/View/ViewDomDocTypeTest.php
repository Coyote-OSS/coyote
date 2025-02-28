<?php
namespace Tests\Legacy\IntegrationNew\BaseFixture\Neon\View;

use PHPUnit\Framework\TestCase;

class ViewDomDocTypeTest extends TestCase
{
    /**
     * @test
     */
    public function html5(): void
    {
        $dom = new ViewDom('<!DOCTYPE html><html></html>');
        $this->assertSame('<!DOCTYPE html>', $dom->docType());
    }

    /**
     * @test
     */
    public function traditionalHtml(): void
    {
        $dom = new ViewDom('<p>Foo</p>');
        $this->assertSame(
            '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd">',
            $dom->docType());
    }
}
