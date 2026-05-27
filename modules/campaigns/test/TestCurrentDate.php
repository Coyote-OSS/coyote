<?php
namespace Test\Modules\Campaigns;

use Modules\Campaigns\ForCurrentDate;

class TestCurrentDate implements ForCurrentDate {
    public function __construct(private ?string $currentDate = null) {}

    public function stubCurrentDate(string $currentDate): void {
        $this->currentDate = $currentDate;
    }

    public function isRangeActive(string $since, string $until): bool {
        return $this->hasStarted($since) && $this->hasNotFinished($until);
    }

    public function hasStarted(string $startDate): bool {
        $this->validate();
        return $startDate < $this->currentDate;
    }

    public function hasNotFinished(string $endDate): bool {
        $this->validate();
        return $this->currentDate < $endDate;
    }

    private function validate(): void {
        if ($this->currentDate === null) {
            throw new \Exception('Failed to determine range active, current date not set.');
        }
    }
}
