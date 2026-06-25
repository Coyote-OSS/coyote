<?php
namespace Tests\Integration\Modules\Campaigns\Adm\View;

use Coyote\Modules\Campaigns\Adm\View\CampaignStats;
use Coyote\Modules\Campaigns\Adm\View\VariantViewModel;
use Modules\Campaigns\VariantType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(VariantViewModel::class)]
#[CoversClass(CampaignStats::class)]
class VariantViewModelTest extends TestCase {
    #[Test]
    public function emptyCtr(): void {
        $this->assertNull($this->ctr(views:0, clicks:0));
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

    #[Test]
    public function bannerTypeTitle_horizontal(): void {
        $this->assertSame('Banner', $this->type(VariantType::Horizontal)->bannerTypeTitle());
    }

    #[Test]
    public function bannerTypeTitle_sidebar(): void {
        $this->assertSame('Rectangle', $this->type(VariantType::Sidebar)->bannerTypeTitle());
    }

    #[Test]
    public function expectedDimension_horizontal(): void {
        $this->assertSame('728 × 90', $this->type(VariantType::Horizontal)->expectedDimension());
    }

    #[Test]
    public function expectedDimension_sidebar(): void {
        $this->assertSame('300 × 250', $this->type(VariantType::Sidebar)->expectedDimension());
    }

    #[Test]
    public function bannerType_horizontal(): void {
        $this->assertSame('horizontal', $this->type(VariantType::Horizontal)->bannerType());
    }

    #[Test]
    public function bannerType_sidebar(): void {
        $this->assertSame('sidebar', $this->type(VariantType::Sidebar)->bannerType());
    }

    private function ctr(int $views, int $clicks): ?string {
        $viewModel = new VariantViewModel('', new CampaignStats($views, $clicks), VariantType::Horizontal);
        return $viewModel->stats->ctr();
    }

    private function type(VariantType $type): VariantViewModel {
        return new VariantViewModel('', new CampaignStats(-1, -1), $type);
    }
}
