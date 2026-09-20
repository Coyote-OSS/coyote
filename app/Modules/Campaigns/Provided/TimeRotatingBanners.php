<?php
namespace Coyote\Modules\Campaigns\Provided;

use Illuminate\Contracts\Cache;
use Modules\Campaigns\ForRotatingBanners;
use Psr\Clock\ClockInterface;

readonly class TimeRotatingBanners implements ForRotatingBanners {
    private const string CACHE_KEY = 'campaigns:harness-rotation-seed';

    public function __construct(
        private ClockInterface   $clock,
        private Cache\Repository $cache,
    ) {}

    public function overrideSeed(int $seed): void {
        $this->cache->put(self::CACHE_KEY, $seed);
    }

    public function rotationSeed(): int {
        return $this->cache->get(self::CACHE_KEY) ?? $this->clock->now()->getTimestamp();
    }
}
