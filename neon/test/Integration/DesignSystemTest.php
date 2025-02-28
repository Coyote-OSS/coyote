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
        $this->renderHtmlInDesignSystem('<p>Hello</p>');
        $this->assertSame('0px', $this->cssProperty('p', 'margin-bottom'));
    }

    private function renderHtmlInDesignSystem(string $code): void
    {
        $query = \http_build_query(['htmlMarkup' => $code]);
        $this->dsl->driver->web->navigate('/DesignSystem?' . $query);
    }

    private function cssProperty(string $cssSelector, string $cssProperty): string
    {
        return $this->dsl->driver->web->find($cssSelector)->cssProperty($cssProperty);
    }
}
