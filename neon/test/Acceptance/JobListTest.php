<?php
namespace Neon\Test\Acceptance;

use Neon\Test\Integration\Dsl;
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class JobListTest extends TestCase
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
    public function jobboardDisplaysBrandHeading(): void
    {
        $this->dsl->driver->visitJobList();
        $this->assertContains('Oferty Pracy w IT - Odwiedza nas ponad 150 tys. programistów miesięcznie',
            $this->dsl->driver->textNodes());
    }
}
