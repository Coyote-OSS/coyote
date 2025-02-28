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
    public function browserStylesAreReset(): void
    {
        $this->dsl->designSystem->renderHtml('<p>Hello</p>');
        $margin = $this->dsl->designSystem->cssProperty('p', 'margin-bottom');
        $this->assertSame('0px', $margin);
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
}
