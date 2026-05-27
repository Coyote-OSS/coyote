<?php
namespace Coyote\Modules\Campaigns;

use Modules\Campaigns\ForRotatingBanners;
use Psr\Clock\ClockInterface;

readonly class TimeRotatingBanners implements ForRotatingBanners {
    public function __construct(private ClockInterface $clock) {}

    public function rotationSeed(): int {
        return $this->clock->now()->getTimestamp();
    }
}
