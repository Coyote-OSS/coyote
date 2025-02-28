<?php
namespace Tests\Legacy\IntegrationNew\BaseFixture\Neon\View;

use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\TestCase;

class ViewDomXPathTest extends TestCase
{
    /**
     * @test
     */
    public function invalid(): void
    {
        $dom = new ViewDom('<p>');
        $exception = $this->caught(fn() => $dom->findString('.invalid'));
        $this->assertSame('Failed to execute malformed xPath: .invalid', $exception->getMessage());
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
