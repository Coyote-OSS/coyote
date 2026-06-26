<?php
namespace Coyote\Modules\Campaigns\Provided;

use Carbon\Carbon;
use Modules\Campaigns\ForCurrentDate;

class CarbonCurrentDate implements ForCurrentDate {
    public function hasStarted(string $startDate): bool {
        return $this->parsed($startDate) < Carbon::now();
    }

    public function hasNotFinished(string $endDate): bool {
        return Carbon::now() < $this->parsed($endDate);
    }

    private function parsed(string $date): Carbon {
        try {
            return Carbon::parse($date);
        } catch (\Throwable) {
            throw new \Exception('Failed to determine range active, date malformed.');
        }
    }
}
