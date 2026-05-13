<?php
namespace Tests\Integration\Modules\Campaigns;

use Coyote\Modules\Campaigns\TimeRotatingBanners;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Legacy\IntegrationNew\BaseFixture;

#[CoversClass(TimeRotatingBanners::class)]
class TimeRotatingBannersTest extends TestCase {
    use BaseFixture\Server\Http;

    private TimeRotatingBanners $rotate;
    private TestClock $clock;

    #[Before]
    public function initialize(): void {
        $this->clock = new TestClock();
        $this->rotate = new TimeRotatingBanners($this->clock);
    }

    #[Test]
    public function returnsTheOnlyBanner(): void {
        $this->assertEquals('foo.png', $this->rotate->rotateBanners(['foo.png']));
    }

    #[Test]
    public function failsForEmptyBanners(): void {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Failed to rotate empty banners.');
        $this->rotate->rotateBanners([]);
    }

    #[Test]
    public function returnsTheFirstBanner(): void {
        $this->clock->advanceTime(1000);
        $banner = $this->rotate->rotateBanners(['first.png', 'second.png']);
        $this->assertEquals('first.png', $banner);
    }

    #[Test]
    public function returnsTheSecondBanner(): void {
        $this->clock->advanceTime(1001);
        $banner = $this->rotate->rotateBanners(['first.png', 'second.png']);
        $this->assertEquals('second.png', $banner);
    }
}
