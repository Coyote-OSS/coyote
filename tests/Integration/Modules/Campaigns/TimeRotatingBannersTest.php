<?php
namespace Tests\Integration\Modules\Campaigns;

use Coyote\Modules\Campaigns\TimeRotatingBanners;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(TimeRotatingBanners::class)]
class TimeRotatingBannersTest extends TestCase {
    private TimeRotatingBanners $rotate;
    private TestClock $clock;

    #[Before]
    public function initialize(): void {
        $this->clock = new TestClock();
        $this->rotate = new TimeRotatingBanners($this->clock);
    }

    #[Test]
    public function returnsRotationSeed_basedOnTime(): void {
        $this->clock->advanceTime(1000);
        $this->assertSame(1000, $this->rotate->rotationSeed());
    }
}
