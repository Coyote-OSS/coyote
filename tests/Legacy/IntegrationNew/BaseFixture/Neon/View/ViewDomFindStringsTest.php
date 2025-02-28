<?php
namespace Tests\Legacy\IntegrationNew\BaseFixture\Neon\View;

use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\TestCase;

class ViewDomFindStringsTest extends TestCase
{
    /**
     * @test
     */
    public function textContents(): void
    {
        $dom = new ViewDom('<ul>
            <li>Ours is the fury</li>
            <li>We do not sow</li>
        <ul>');
        $this->assertThat(
            $dom->findStrings('/html/body/ul/li/text()'),
            $this->equalTo([
                'Ours is the fury',
                'We do not sow',
            ]),
        );
    }

    /**
     * @test
     */
    public function throwForElement(): void
    {
        $dom = new ViewDom('<ul></ul>');
        $exception = $this->caught(fn() => $dom->findStrings('/html/body/ul'));
        $this->assertSame('Failed to get element as string: <ul>', $exception->getMessage());
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
}
