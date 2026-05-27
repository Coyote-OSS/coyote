<?php
namespace Tests\Integration\Modules\Campaigns\Adm\View;

use Coyote\Modules\Campaigns\Adm\View\CampaignPresenter;
use Modules\Campaigns\CampaignService;
use Modules\Campaigns\InMemoryCampaignsStore;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Test\Modules\Campaigns\TestCurrentDate;
use Test\Modules\Campaigns\TestPrivilegedUsers;
use Test\Modules\Campaigns\TestRotatingBanners;

#[CoversClass(CampaignPresenter::class)]
class CampaignPresenterTest extends TestCase {
    private InMemoryCampaignsStore $store;
    private TestCurrentDate $date;
    private CampaignPresenter $presenter;
    private CampaignService $campaigns;

    #[Before]
    public function beforeEach(): void {
        $this->store = new InMemoryCampaignsStore();
        $this->date = new TestCurrentDate();
        $this->campaigns = new CampaignService(
            new TestPrivilegedUsers(),
            new TestRotatingBanners(),
            $this->date,
            $this->store);
        $this->presenter = new CampaignPresenter($this->store, $this->campaigns);
    }

    #[Test]
    public function sidebarViewModel_imageUrl(): void {
        $viewModel = $this->presenter->sidebarViewModel('', 'image.png');
        $this->assertEquals('image.png', $viewModel->imageUrl);
    }

    #[Test]
    public function horizontalViewModel_imageUrl(): void {
        $viewModel = $this->presenter->horizontalViewModel('', 'image.png');
        $this->assertEquals('image.png', $viewModel->imageUrl);
    }

    #[Test]
    public function horizontalViewModel_clicks(): void {
        $this->store->stubCampaignClicks(13, 'horizontal');
        $vm = $this->presenter->horizontalViewModel('foo', '');
        $this->assertEquals(13, $vm->stats->clicks);
    }

    #[Test]
    public function horizontalViewModel_views(): void {
        $this->store->stubCampaignViews(12, 'horizontal');
        $vm = $this->presenter->horizontalViewModel('foo', '');
        $this->assertEquals(12, $vm->stats->views);
    }

    #[Test]
    public function campaignViews_isSumOfBannerViews(): void {
        $this->store->stubCampaignViews(12, 'horizontal');
        $this->store->stubCampaignViews(13, 'sidebar');
        $vm = $this->presenter->campaignStats('foo');
        $this->assertEquals(25, $vm->views);
    }

    #[Test]
    public function campaignClicks_isSumOfBannerClicks(): void {
        $this->store->stubCampaignClicks(11, 'horizontal');
        $this->store->stubCampaignClicks(12, 'sidebar');
        $vm = $this->presenter->campaignStats('foo');
        $this->assertEquals(23, $vm->clicks);
    }

    #[Test]
    public function campaignIsActive_whenBothSinceAndUntilAreSet(): void {
        $this->date->stubCurrentDate('2000-01-01T00:00:00');
        $this->stubCampaignActiveRange('campaign', '1970', '2200');
        $this->assertTrue($this->presenter->campaignActive('campaign'));
    }

    private function stubCampaignActiveRange(
        string  $campaignKey,
        ?string $activeSince,
        ?string $activeUntil,
    ): void {
        $this->campaigns->add('', '', $campaignKey, '', $activeSince, $activeUntil, 999);
    }
}
