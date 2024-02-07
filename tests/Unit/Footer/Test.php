<?php
namespace Tests\Unit\Footer;

use PHPUnit\Framework\TestCase;
use Tests\Unit\Footer;

class Test extends TestCase
{
    use Footer\Fixture\FooterStatements;
    use Footer\Fixture\Clock;

    /**
     * @test
     */
    public function copyrightYear()
    {
        $this->systemYear(2005);
        $this->assertThat($this->footerStatements(), $this->containsIdentical('Copyright © 2000-2005'));
    }
}
