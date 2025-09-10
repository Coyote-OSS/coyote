<?php
namespace Coyote\Http\Controllers\Adm;

use Illuminate\Http\Response;

class CohortController {
    public function download(): Response {
        $date = date('Y-m-d');
        return $this->downloadFile(
            "4programmers.cohort.$date.csv",
            'text/csv',
            $this->recordsToCsvString([
                ['Name', 'Age', 'City'],
                ['Alice', 30, 'London'],
                ['Bob', 25, 'New York'],
                ['Charlie', 35, 'Paris'],
            ]));
    }

    private function downloadFile(string $filename, string $contentType, string $content): Response {
        return response($content)
            ->header('Content-Type', $contentType)
            ->header('Content-Disposition', "attachment; filename=\"$filename\"");
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
}
