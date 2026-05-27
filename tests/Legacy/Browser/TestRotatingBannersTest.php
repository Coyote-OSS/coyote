<?php
namespace Tests\Legacy\Browser;

use Modules\Campaigns\BannerRotation;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Test\Modules\Campaigns\TestRotatingBanners;

#[CoversClass(TestRotatingBanners::class)]
#[CoversClass(BannerRotation::class)]
class TestRotatingBannersTest extends TestCase {
    private TestRotatingBanners $rotate;

    #[Before]
    public function initialize(): void {
        $this->rotate = new TestRotatingBanners();
    }

    #[Test]
    public function failToAcceptNegativeAmount(): void {
        $this->expectException(\Exception::class);
        $this->rotate->rotatedBanners([], -1);
    }

    #[Test]
    public function returnEmpty(): void {
        $this->assertSame([], $this->rotate->rotatedBanners([], 0));
    }

    #[Test]
    public function acceptSuperfluousAmountSingle(): void {
        $this->assertSame(['foo'], $this->rotate->rotatedBanners(['foo'], 2));
    }

    #[Test]
    public function acceptSuperfluousAmountMany(): void {
        $this->assertSame(['foo', 'bar'], $this->rotate->rotatedBanners(['foo', 'bar'], 4));
    }

    #[Test]
    public function returnSingle(): void {
        $this->assertSame(['foo'], $this->rotate->rotatedBanners(['foo'], 1));
    }

    #[Test]
    public function returnEmpty_ifAmountIs0(): void {
        $this->assertSame([], $this->rotate->rotatedBanners(['foo'], 0));
    }

    #[Test]
    public function returnSingle_ifAmountIs1(): void {
        $this->assertSame(['foo'], $this->rotate->rotatedBanners(['foo', 'bar'], 1));
    }

    #[Test]
    public function rotateTwo_once(): void {
        $this->rotate->rotate();
        $this->assertSame(['bar'], $this->rotate->rotatedBanners(['foo', 'bar'], 1));
    }

    #[Test]
    public function rotateTwo_twice(): void {
        $this->rotate->rotate();
        $this->rotate->rotate();
        $this->assertSame(['foo'], $this->rotate->rotatedBanners(['foo', 'bar'], 1));
    }

    #[Test]
    public function rotateThree_never(): void {
        $this->assertSame(['foo', 'bar'], $this->rotate->rotatedBanners(['foo', 'bar', 'cat'], 2));
    }

    #[Test]
    public function rotateThree_once(): void {
        $this->rotate->rotate();
        $this->assertSame(['bar', 'cat'], $this->rotate->rotatedBanners(['foo', 'bar', 'cat'], 2));
    }

    #[Test]
    public function rotateThree_twice(): void {
        $this->rotate->rotate();
        $this->rotate->rotate();
        $this->assertSame(['cat', 'foo'], $this->rotate->rotatedBanners(['foo', 'bar', 'cat'], 2));
    }

    #[Test]
    public function rotateThree_thrice(): void {
        $this->rotate->rotate();
        $this->rotate->rotate();
        $this->rotate->rotate();
        $this->assertSame(['foo', 'bar'], $this->rotate->rotatedBanners(['foo', 'bar', 'cat'], 2));
    }

    #[Test]
    public function rotateOne_once(): void {
        $this->rotate->rotate();
        $this->assertSame(['foo'], $this->rotate->rotatedBanners(['foo'], 1));
    }

    #[Test]
    public function rotateOne_twice(): void {
        $this->rotate->rotate();
        $this->rotate->rotate();
        $this->assertSame(['foo'], $this->rotate->rotatedBanners(['foo'], 1));
    }
}
