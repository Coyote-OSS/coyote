<?php
namespace Modules\Campaigns;

interface ForCurrentDate {
    public function hasStarted(string $startDate): bool;

    public function hasNotFinished(string $endDate): bool;
}
