<?php
namespace Tests\Legacy\IntegrationNew\BaseFixture\Neon\View;

use PHPUnit\Framework\TestCase;

class ViewDomExistsTest extends TestCase
{
    /**
     * @test
     */
    public function existing(): void
    {
        $dom = new ViewDom('<div><img/></div>');
        $this->assertTrue($dom->exists('//div/img'));
    }

    /**
     * @test
     */
    public function missing(): void
    {
        $dom = new ViewDom('<div></div>');
        $this->assertFalse($dom->exists('//div/img'));
    }
}
