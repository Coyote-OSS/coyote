<?php
namespace Service\Cohort;

use Coyote\Services\Adm\Cohort\CohortRetention;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class CohortRetentionTest extends TestCase {
    #[Test]
    public function formatFractionAsPercentage(): void {
        $retention = new CohortRetention('2000-01-01', [2, 1, 0]);
        $this->assertSame(['100%', '50%', '0%'], $retention->toFractionRetention());
    }

    #[Test]
    public function formatUpToOneDecimalPlace(): void {
        $retention = new CohortRetention('2000-01-01', [3, 1, 0]);
        $this->assertSame(['100%', '33.3%', '0%'], $retention->toFractionRetention());
    }

    #[Test]
    public function acceptRetentionInTheFuture(): void {
        $retention = new CohortRetention('2000-01-01', [3, 1, null]);
        $this->assertSame(['100%', '33.3%', null], $retention->toFractionRetention());
    }
}
