<?php
namespace Service\Cohort;

use Coyote\Services\Adm\Cohort\CohortRetention;
use Coyote\Services\Adm\Cohort\CohortService;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class CohortTest extends TestCase {
    private CohortService $service;

    #[Before]
    public function initialize(): void {
        $this->service = new CohortService();
    }

    #[Test]
    public function whichMonthUserFirstSeen(): void {
        $firstSeen = $this->service->firstSeen([
            'd1' => ['u1', 'u2'],
            'd2' => ['u2', 'u3'],
            'd3' => ['u1', 'u3', 'u4'],
        ]);
        $expected = [
            'u1' => 'd1',
            'u2' => 'd1',
            'u3' => 'd2',
            'u4' => 'd3',
        ];
        $this->assertEquals($expected, $firstSeen);
    }

    #[Test]
    public function groupUsersIntoCohorts(): void {
        $cohorts = $this->service->groupCohorts([
            'd1' => ['u1', 'u2'],
            'd2' => ['u2', 'u3'],
            'd3' => ['u1', 'u3', 'u4', 'u5'],
        ]);
        $expected = [
            'd1' => ['u1', 'u2'],
            'd2' => ['u3'],
            'd3' => ['u4', 'u5'],
        ];
        $this->assertEquals($expected, $cohorts);
    }

    #[Test]
    public function calculateRetainedUsersForEachCohort_inEachAgeOfHorizon(): void {
        $grid = $this->service->retentionGrid(3, [
            'd1' => ['u1', 'u2'],
            'd2' => ['u2', 'u3'],
            'd3' => ['u1', 'u3', 'u4'],
        ]);
        $expectedGrid = [
            'd1' => [
                ['u1', 'u2'],
                ['u2'],
                ['u1'],
            ],
            'd2' => [
                ['u3'],
                ['u3'],
                null,
            ],
            'd3' => [
                ['u4'],
                null,
                null,
            ],
        ];
        $this->assertSame($expectedGrid, $grid);
    }

    #[Test]
    public function collapseRetentionGridUsersToStats(): void {
        $grid = $this->service->retentionGridStats(3, [
            '2000-01-01' => ['u1', 'u2'],
            '2000-02-01' => ['u2', 'u3'],
            '2000-03-01' => ['u1', 'u3', 'u4'],
        ]);
        $expectedGrid = [
            new CohortRetention('2000-01-01', [2, 1, 1]),
            new CohortRetention('2000-02-01', [1, 1, null]),
            new CohortRetention('2000-03-01', [1, null, null]),
        ];
        $this->assertEquals($expectedGrid, $grid);
    }
}
