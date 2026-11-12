<?php
namespace Coyote\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Connection;

class RollupContentVisitsCommand extends Command {
    protected $signature = 'visits:rollup';
    protected $description = 'Move content visits from past months into monthly rollup';

    public function handle(Connection $db): int {
        $currentMonth = Carbon::now()->startOfMonth();
        $db->transaction(function () use ($db, $currentMonth): void {
            $db->insert("
                INSERT INTO content_visits_monthly (month, user_id, guest_id, content_type, visits)
                SELECT date_trunc('month', created_at)::date, user_id, guest_id, content_type, count(*)
                FROM content_visits
                WHERE created_at < ?
                GROUP BY 1, 2, 3, 4",
                [$currentMonth]);
            $db->table('content_visits')
                ->where('created_at', '<', $currentMonth)
                ->delete();
        });
        return 0;
    }
}
