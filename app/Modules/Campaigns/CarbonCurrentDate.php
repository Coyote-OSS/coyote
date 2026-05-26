<?php
namespace Coyote\Modules\Campaigns;

use Carbon\Carbon;
use Modules\Campaigns\ForCurrentDate;

class CarbonCurrentDate implements ForCurrentDate {
    public function isRangeActive(string $since, string $until): bool {
        return $this->isNowBetween($this->parsed($since), $this->parsed($until));
    }

    private function isNowBetween(Carbon $since, Carbon $after): bool {
        $now = Carbon::now();
        return $since < $now && $now < $after;
    }

    private function parsed(string $date): Carbon {
        try {
            return Carbon::parse($date);
        } catch (\Throwable) {
            throw new \Exception('Failed to determine range active, date malformed.');
        }
    }
}
