<?php
namespace Test\Modules\Campaigns;

use Modules\Campaigns\VariantType;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class VariantTypeTest extends TestCase {
    #[Test]
    public function standard(): void {
        $this->assertSame(VariantType::Banner, VariantType::fromSize(728, 90));
    }

    #[Test]
    public function sidebar(): void {
        $this->assertSame(VariantType::Rectangle, VariantType::fromSize(300, 250));
    }

    #[Test]
    public function leaderBoard(): void {
        $this->assertSame(VariantType::LeaderBoard, VariantType::fromSize(1140, 90));
    }

    #[Test]
    public function standardXl(): void {
        $this->assertSame(VariantType::BannerXl, VariantType::fromSize(728, 200));
    }

    #[Test]
    public function sidebarXl(): void {
        $this->assertSame(VariantType::RectangleXl, VariantType::fromSize(300, 600));
    }

    #[Test]
    public function leaderBoardXl(): void {
        $this->assertSame(VariantType::LeaderBoardXl, VariantType::fromSize(1140, 200));
    }

    #[Test]
    public function unmatchedSizeReturnsNull(): void {
        $this->assertNull(VariantType::fromSize(1, 1));
    }
}
