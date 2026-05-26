<?php
namespace Modules\Campaigns;

interface ForCurrentDate {
    public function isRangeActive(string $since, string $until): bool;
}
