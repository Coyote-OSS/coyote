<?php
namespace Coyote\Domain\Registration;

use Coyote\User;
use Illuminate\Support\Facades\DB;

readonly class SingleUserActivity implements ChartSource {
    private UniformDates $uniformDates;

    public function __construct(private User $user) {
        $this->uniformDates = new UniformDates();
    }

    public function id(): string {
        return 'streams.created_at.user';
    }

    public function title(): string {
        return 'Historia aktywności użytkownika';
    }

    public function inRange(HistoryRange $range): array {
        return \array_merge(
            $this->arrayFrom(
                keys:$this->uniformDates->inRange($range),
                value:0),
            $this->fetchRegistrationsByPeriod($range->startDate(), $range->endDate(), $range->period));
    }

    private function fetchRegistrationsByPeriod(string $from, string $to, Period $period): array {
        $dateTruncSqlField = $this->dateTruncSqlField('created_at', $period);
        return DB::table('streams')
            ->where('created_at', '>=', "$from 00:00:00")
            ->where('created_at', '<', "$to 24:00:00")
            ->whereRaw("(streams.actor ->> 'id')::int = ?", [$this->user->id])
            ->selectRaw("$dateTruncSqlField as created_at_group, count(*) AS count")
            ->groupByRaw($dateTruncSqlField)
            ->get()
            ->pluck('count', 'created_at_group')
            ->toArray();
    }

    private function dateTruncSqlField(string $column, Period $period): string {
        return match ($period) {
            Period::Day   => "date_trunc('day', $column)::date",
            Period::Week  => "date_trunc('week', $column)::date",
            Period::Month => "date_trunc('month', $column)::date",
            Period::Year  => "date_trunc('year', $column)::date",
        };
    }

    private function arrayFrom(array $keys, int $value): array {
        $values = \array_fill(0, \count($keys), $value);
        return \array_combine($keys, $values);
    }
}
