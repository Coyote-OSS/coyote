<?php
namespace Coyote\Modules\Campaigns;

use Modules\Campaigns\ForRotatingBanners;
use Psr\Clock\ClockInterface;

readonly class TimeRotatingBanners implements ForRotatingBanners {
    public function __construct(private ClockInterface $clock) {}

    public function rotateBanners(array $banners): string {
        if (empty($banners)) {
            throw new \Exception('Failed to rotate empty banners.');
        }
        return $banners[$this->clock->now()->getTimestamp() % \count($banners)];
    }
}
