<?php
namespace Boduch\Grid\Decorators;

use Boduch\Grid\Cell;
use Carbon\Carbon;

class FormatDateRelative extends Decorator
{
    public function __construct(private string $default, private bool $shortDate = false) {}

    public function decorate(Cell $cell): void
    {
        $initialValue = $cell->getUnescapedValue();
        if ($initialValue) {
            $cell->setValue($this->formatDateTime($initialValue));
        } else {
            $cell->setValue($this->default);
        }
    }

    protected function formatDateTime(string $dateTime): string
    {
        $date = Carbon::parse($dateTime);
        $now = Carbon::now();
        $diff = $now->diffForHumans($date, syntax:true);
        if ($date->isBefore($now)) {
            return $this->formatBefore($diff);
        }
        return "za $diff";
    }

    private function formatBefore(string $differance): string
    {
        if ($this->shortDate) {
            return $differance;
        }
        return "$differance temu";
    }
}
