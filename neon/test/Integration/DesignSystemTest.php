<?php
namespace Neon\Test\Integration;

use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class DesignSystemTest extends TestCase
{
    private Dsl $dsl;

    #[Before]
    public function initializeDsl(): void
    {
        $this->dsl = new Dsl();
    }

    #[After]
    public function finalize(): void
    {
        if ($this->status()->isFailure() || $this->status()->isError()) {
            $this->dsl->includeDiagnosticArtifact($this);
        }
        $this->dsl->close();
    }

    #[Test]
    public function backgroundIsDark_inDarkTheme(): void
    {
        $this->dsl->designSystem->setTheme('dark');
        $this->assertSame('#121314', $this->dsl->designSystem->backgroundColor());
    }

    #[Test]
    public function backgroundIsLight_inLightTheme(): void
    {
        $this->dsl->designSystem->setTheme('light');
        $this->assertSame('#f0f2f5', $this->dsl->designSystem->backgroundColor());
    }

    #[Test]
    public function browserStylesAreReset(): void
    {
        $this->dsl->designSystem->showSection('reset-elements');
        $this->assertSame('0px', $this->cssProperty('p', 'margin-bottom'));
        $this->assertSame('#00000000', $this->dsl->designSystem->cssColor('button', 'background-color'));
    }

    #[Test]
    public function tailwindSpacing(): void
    {
        $this->dsl->designSystem->showSection('spacing-preview');
        $this->assertSame('4px', $this->cssProperty('p:nth-child(1)', 'margin-bottom'));
        $this->assertSame('8px', $this->cssProperty('p:nth-child(2)', 'margin-bottom'));
        $this->assertSame('12px', $this->cssProperty('p:nth-child(3)', 'margin-bottom'));
        $this->assertSame('16px', $this->cssProperty('p:nth-child(4)', 'margin-bottom'));
    }

    #[Test]
    public function responsiveElements(): void
    {
        $this->dsl->designSystem->showSection('responsive-elements');
        $this->assertSame('block', $this->cssProperty('aside', 'display', viewport:430));
        $this->assertSame('none', $this->cssProperty('aside', 'display', viewport:1440));
    }

    private function cssProperty(string $cssSelector, string $cssProperty, ?int $viewport = null): string
    {
        if ($viewport) {
            $this->dsl->designSystem->setViewportWidth($viewport);
        }
        return $this->dsl->designSystem->cssProperty($cssSelector, $cssProperty);
    }
}
