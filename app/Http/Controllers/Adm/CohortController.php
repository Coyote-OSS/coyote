<?php
namespace Coyote\Http\Controllers\Adm;

use Coyote\Services\Adm\Cohort\CohortRetention;
use Coyote\Services\Adm\Cohort\CohortService;
use Illuminate\Database\Connection;
use Illuminate\Http\Response;

class CohortController {
    public function download(CohortService $cohort, Connection $connection): Response {
        $date = date('Y-m-d');
        $activeByMonths = $this->userStreamActivityByMonth($connection);
        $cohortRetentions = $cohort->retentionGridStats(24, $activeByMonths);
        $last2Years = \array_slice($cohortRetentions, -24);
        $records = $this->formatCohortRetentions($last2Years);
        return $this->downloadFile(
            "4programmers.cohort.$date.csv",
            'text/csv',
            $this->recordsToCsvString($records));
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
        return [$retention->date, ...$retention->toFractionRetention()];
    }
}
