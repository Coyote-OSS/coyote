<?php
namespace Tests\Integration\Modules\Campaigns;

use DateTimeImmutable;
use Psr\Clock\ClockInterface;

class TestClock implements ClockInterface {
    private int $time = 0;

    public function now(): DateTimeImmutable {
        return new \DateTimeImmutable("@$this->time");
    }

    public function advanceTime(int $time): void {
        $this->time = $time;
    }
}
