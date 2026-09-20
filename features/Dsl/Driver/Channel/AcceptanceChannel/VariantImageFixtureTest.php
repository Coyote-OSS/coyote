<?php
namespace Features\Dsl\Driver\Channel\AcceptanceChannel;

use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class VariantImageFixtureTest extends TestCase {
    private string $path;

    #[Test]
    public function banner(): void {
        $this->assertImageDimensions('banner', 728, 90);
    }

    #[Test]
    public function bannerXl(): void {
        $this->assertImageDimensions('banner-xl', 728, 200);
    }

    #[Test]
    public function leaderboard(): void {
        $this->assertImageDimensions('leaderboard', 1140, 90);
    }

    #[Test]
    public function leaderboardXl(): void {
        $this->assertImageDimensions('leaderboard-xl', 1140, 200);
    }

    #[Test]
    public function rectangle(): void {
        $this->assertImageDimensions('rectangle', 300, 250);
    }

    #[Test]
    public function rectangleXl(): void {
        $this->assertImageDimensions('rectangle-xl', 300, 600);
    }

    private function assertImageDimensions(
        string $variantType,
        int    $expectedWidth,
        int    $expectedHeight,
    ): void {
        $this->path = new VariantImageFixture()->create($variantType);
        [$actualWidth, $actualHeight] = \getImageSize($this->path);
        $this->assertSame($expectedWidth, $actualWidth);
        $this->assertSame($expectedHeight, $actualHeight);
    }

    #[After]
    public function removeCreatedFile(): void {
        \unlink($this->path);
    }
}
