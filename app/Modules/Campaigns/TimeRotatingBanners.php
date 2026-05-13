<?php
namespace Coyote\Modules\Campaigns;

use Modules\Campaigns\ForRotatingBanners;
use Psr\Clock\ClockInterface;

readonly class TimeRotatingBanners implements ForRotatingBanners {
    public function __construct(private ClockInterface $clock) {}

    public function rotateBanners(array $campaignKeys): string {
        if (empty($campaignKeys)) {
            throw new \Exception('Failed to rotate empty banners.');
        }
        return $campaignKeys[$this->clock->now()->getTimestamp() % \count($campaignKeys)];
    }
}
