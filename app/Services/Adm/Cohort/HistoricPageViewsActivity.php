<?php
namespace Coyote\Services\Adm\Cohort;

use Coyote\Pm;
use Illuminate\Database\Connection;

readonly class HistoricPageViewsActivity {
    public function __construct(private Connection $connection) {}

    public function lowerBound(string $since): array {
        return $this->activity($since, "
            SELECT date_trunc('month', at)::date AS month, json_agg(DISTINCT user_id) AS user_ids
            FROM evidence
            GROUP BY 1
            ORDER BY 1");
    }

    public function upperBound(string $since): array {
        return $this->activity($since, "
            SELECT month::date AS month, json_agg(user_id) AS user_ids
            FROM (
                SELECT user_id, date_trunc('month', MIN(at)) AS first_month, date_trunc('month', MAX(at)) AS last_month
                FROM evidence
                GROUP BY user_id
            ) span, generate_series(first_month, last_month, INTERVAL '1 month') month
            GROUP BY 1
            ORDER BY 1");
    }

    private function activity(string $since, string $select): array {
        $since = new \DateTimeImmutable($since)->format('Y-m-d'); // inlined, so must be a valid date
        $rows = $this->connection->select("
            WITH cohort AS (
                SELECT id FROM users WHERE created_at >= DATE '$since'
            ),
            traces (user_id, at) AS ({$this->traces($since)}),
            evidence AS (
                SELECT traces.user_id, traces.at
                FROM traces
                JOIN cohort ON cohort.id = traces.user_id
                WHERE traces.at <= NOW()
            )
            $select");
        $activity = [];
        foreach ($rows as $row) {
            $activity[$row->month] = \json_decode($row->user_ids);
        }
        return $activity;
    }

    private function traces(string $since): string {
        $traces = [
            // [user, time, from, condition]
            ['id', 'created_at', 'users'],
            ['id', 'visited_at', 'users'],
            ["(actor->>'id')::int", 'created_at', 'streams', "actor->>'id' IS NOT NULL"], // logins, edits, ...
            ['user_id', 'created_at', 'posts'],
            ['user_id', 'created_at', 'post_comments'],
            ['user_id', 'created_at', 'microblogs'],
            ['user_id', 'created_at', 'post_votes'],
            ['user_id', 'created_at', 'microblog_votes'],
            ['user_id', 'created_at', 'post_accepts'],
            ['user_id', 'created_at', 'subscriptions'],
            ['user_id', 'created_at', 'flags'],
            ['user_id', 'created_at', 'wiki_rates'],
            ['user_id', 'read_at', 'notifications'],
            ['user_id', 'read_at', 'pm', 'folder = ' . Pm::INBOX], // read_at is set on both copies, but only the recipient read it
            ['user_id', 'created_at', 'page_visits'], // first and last visit of a wiki page
            ['user_id', 'updated_at', 'page_visits'],
            // topics and categories marked as read; overwritten when read again
            ['guests.user_id', 'topic_track.marked_at', 'topic_track JOIN guests ON guests.id = topic_track.guest_id'],
            ['guests.user_id', 'forum_track.marked_at', 'forum_track JOIN guests ON guests.id = forum_track.guest_id'],
            // actual page views, since 2026-11
            ['user_id', 'created_at', 'content_visits'],
            ['user_id', 'month', 'content_visits_monthly'],
        ];
        return \implode("\nUNION ALL\n", \array_map(
            fn(array $trace): string => $this->trace($since, ...$trace),
            $traces));
    }

    private function trace(string $since, string $user, string $at, string $from, ?string $condition = null): string {
        $where = $condition ? "$condition AND " : '';
        return "SELECT $user, $at::timestamptz FROM $from WHERE $where$at >= DATE '$since'";
    }
}
