<?php
namespace Tests\Integration\Modules\Campaigns\Provided;

use Coyote\Modules\Campaigns\Provided\TimeRotatingBanners;
use Illuminate\Cache;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(TimeRotatingBanners::class)]
class TimeRotatingBannersTest extends TestCase {
    private Cache\Repository $cache;
    private TimeRotatingBanners $rotate;
    private TestClock $clock;

    #[Before]
    public function initialize(): void {
        $this->clock = new TestClock();
        $this->cache = new Cache\Repository(new Cache\ArrayStore());
        $this->rotate = new TimeRotatingBanners($this->clock, $this->cache);
    }

    #[Test]
    public function returnsRotationSeed_basedOnTime(): void {
        $this->clock->advanceTime(1000);
        $this->assertSame(1000, $this->rotate->rotationSeed());
    }

    #[Test]
    public function withAHarnessOverride_returnsTheOverriddenSeedInsteadOfTheClock(): void {
        $this->rotate->overrideSeed(7);
        $this->assertSame(7, $this->rotate->rotationSeed());
    }

    #[Test]
    public function theOverriddenSeed_isSharedThroughTheCache(): void {
        $this->rotate->overrideSeed(7);
        $other = new TimeRotatingBanners(new TestClock(), $this->cache);
        $this->assertSame(7, $other->rotationSeed());
    }
}
