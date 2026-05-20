<?php
namespace Tests\Integration\Modules\Campaigns\Adm\View;

use Coyote\Modules\Campaigns\Adm\View\BannerViewModel;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class BannerViewModelTest extends TestCase {
    #[Test]
    public function emptyCtr(): void {
        $this->assertSame('?', $this->ctr(views:0, clicks:0));
    }

    #[Test]
    public function zeroClicks(): void {
        $this->assertSame('0.000%', $this->ctr(views:10, clicks:0));
    }

    #[Test]
    public function halfByHalf(): void {
        $this->assertSame('50.000%', $this->ctr(views:10, clicks:5));
    }

    #[Test]
    public function roundToDecimalPlaces(): void {
        $this->assertSame('16.667%', $this->ctr(views:6, clicks:1));
    }

    private function ctr(int $views, int $clicks): string {
        $viewModel = new BannerViewModel($views, $clicks, '');
        return $viewModel->ctr();
    }
}
