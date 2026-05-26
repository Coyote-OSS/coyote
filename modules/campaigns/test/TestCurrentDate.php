<?php
namespace Test\Modules\Campaigns;

use Modules\Campaigns\ForCurrentDate;

class TestCurrentDate implements ForCurrentDate {
    public function __construct(private ?string $currentDate = null) {}

    public function stubCurrentDate(string $currentDate): void {
        $this->currentDate = $currentDate;
    }

    public function isRangeActive(string $since, string $until): bool {
        if ($this->currentDate === null) {
            throw new \Exception('Failed to determine range active, current date not set.');
        }
        return $since < $this->currentDate && $this->currentDate < $until;
    }
}
