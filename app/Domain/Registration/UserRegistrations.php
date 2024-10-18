<?php
namespace Coyote\Domain\Registration;

use Coyote\User;

readonly class UserRegistrations
{
    private UniformDates $uniformDates;

    public function __construct()
    {
        $this->uniformDates = new UniformDates();
    }

    public function inRange(HistoryRange $range): array
    {
        return $this->inRangeColumn($range, 'created_at');
    }

    public function inRangeColumn(HistoryRange $range, string $column): array
    {
        return \array_merge(
            $this->arrayFrom(
                keys:$this->uniformDates->inRange($range),
                value:0),
            $this->fetchRegistrationsByPeriod($column, $range->startDate(), $range->endDate(), $range->period),
        );
    }

    private function fetchRegistrationsByPeriod(string $column, string $from, string $to, Period $period): array
    {
        $dateTruncSqlField = $this->dateTruncSqlField($column, $period);
        return User::withTrashed()
            ->where($column, '>=', "$from 00:00:00")
            ->where($column, '<', "$to 24:00:00")
            ->selectRaw("$dateTruncSqlField as created_at_group, Count(*) AS count")
            ->groupByRaw($dateTruncSqlField)
            ->get()
            ->pluck(key:'created_at_group', value:'count')
            ->toArray();
    }

    private function dateTruncSqlField(string $column, Period $period): string
    {
        return match ($period) {
            Period::Week => "date_trunc('week', $column)::date",
            Period::Month => "date_trunc('month', $column)::date",
            Period::Year => "date_trunc('year', $column)::date",
        };
    }

    private function arrayFrom(array $keys, int $value): array
    {
        $values = \array_fill(0, \count($keys), $value);
        return \array_combine($keys, $values);
    }
}
