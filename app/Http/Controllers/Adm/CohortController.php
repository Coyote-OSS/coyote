<?php
namespace Coyote\Http\Controllers\Adm;

use Coyote\Services\Adm\Cohort\CohortRetention;
use Coyote\Services\Adm\Cohort\CohortService;
use Coyote\Services\Adm\Cohort\HistoricPageViewsActivity;
use Coyote\User;
use Illuminate\Database\Connection;
use Illuminate\Http\Response;

class CohortController {
    public function download(CohortService $cohort, Connection $connection, HistoricPageViewsActivity $estimated): Response {
        if (!$this->user()->can('adm-payment')) {
            abort(403);
        }
        $date = date('Y-m-d');
        $source = $this->cohortActivitySource();
        $cohortRetentions = $cohort->retentionGridStats(
            24,
            $this->userActivityByMonth($connection, $estimated, $source));
        if ($source === 'view') {
            // views are tracked since 2026-11, so the first cohort holds every
            // user active in that month, regardless of when they actually joined
            $cohortRetentions = \array_slice($cohortRetentions, 1);
        }
        $last2Years = \array_slice($cohortRetentions, -24);
        return $this->downloadFile(
            "4programmers.cohort.$date.csv",
            'text/csv',
            $this->recordsToCsvString([
                ['date', 'cohort size'],
                ...$this->formatCohortRetentions($last2Years),
            ]));
    }

    private function cohortActivitySource(): string {
        $by = request()->query('by');
        if (\in_array($by, ['view', 'stream', 'view-method-server', 'view-method-client'])) {
            return $by;
        }
        abort(400);
    }

    private function userActivityByMonth(Connection $connection, HistoricPageViewsActivity $estimated, string $source): array {
        return match ($source) {
            'stream'             => $this->userStreamActivityByMonth($connection),
            'view'               => $this->userViewActivityByMonth($connection),
            'view-method-server' => $estimated->lowerBound($this->firstListedCohort()),
            'view-method-client' => $estimated->upperBound($this->firstListedCohort()),
        };
    }

    /**
     * Estimated cohorts are by registration month, so only users
     * registered within the 24 listed months are needed.
     */
    private function firstListedCohort(): string {
        return new \DateTimeImmutable('first day of this month')->modify('-23 months')->format('Y-m-d');
    }

    private function userViewActivityByMonth(Connection $connection): array {
        $rows = $connection->select("
            SELECT month AS date, json_agg(DISTINCT user_id) AS user_ids
            FROM (
                SELECT month, user_id FROM content_visits_monthly WHERE user_id IS NOT NULL
                UNION ALL
                SELECT date_trunc('month', created_at)::date, user_id FROM content_visits WHERE user_id IS NOT NULL
            ) visits
            GROUP BY month
            ORDER BY month");
        $activity = [];
        foreach ($rows as $row) {
            $activity[$row->date] = \json_decode($row->user_ids);
        }
        return $activity;
    }

    private function userStreamActivityByMonth(Connection $connection): array {
        return $connection->table('streams')
            ->selectRaw("
                date_trunc('month', created_at)::date AS date,
                json_agg(DISTINCT (actor->>'id')::int) AS user_ids
            ")
            ->whereRaw("actor->>'id' IS NOT NULL")
            ->groupByRaw("date_trunc('month', created_at)::date")
            ->orderByRaw("date_trunc('month', created_at)::date")
            ->pluck('user_ids', 'date')
            ->map(\json_decode(...))
            ->toArray();
    }

    private function recordsToCsvString(array $records): string {
        $handle = \fOpen('php://temp', 'r+');
        foreach ($records as $record) {
            \fPutCsv($handle, $record);
        }
        \rewind($handle);
        try {
            return \stream_get_contents($handle);
        } finally {
            \fClose($handle);
        }
    }

    private function downloadFile(string $filename, string $contentType, string $content): Response {
        return response($content)
            ->header('Content-Type', $contentType)
            ->header('Content-Disposition', "attachment; filename=\"$filename\"");
    }

    /**
     * @param CohortRetention[] $retentions
     */
    private function formatCohortRetentions(array $retentions): array {
        return \array_map($this->formatCohortRetention(...), $retentions);
    }

    private function formatCohortRetention(CohortRetention $retention): array {
        return [
            $retention->date,
            $retention->cohortSize(),
            ...$retention->toFractionRetention(),
        ];
    }

    private function user(): User {
        /** @var User $user */
        $user = auth()->user();
        return $user;
    }
}
