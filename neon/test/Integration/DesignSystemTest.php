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
}
