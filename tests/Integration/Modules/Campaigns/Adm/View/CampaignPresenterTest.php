<?php
namespace Modules\Campaigns\Adm\View;

use Coyote\Modules\Campaigns\Adm\View\CampaignPresenter;
use Modules\Campaigns\InMemoryCampaignsStore;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(CampaignPresenter::class)]
class CampaignPresenterTest extends TestCase {
    private InMemoryCampaignsStore $store;
    private CampaignPresenter $presenter;

    #[Before]
    public function beforeEach(): void {
        $this->store = new InMemoryCampaignsStore();
        $this->presenter = new CampaignPresenter($this->store);
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
        $this->store->stubCampaignActiveRange('campaign', '1970', '1970');
        $this->assertTrue($this->presenter->campaignActive('campaign'));
    }

    #[Test]
    public function campaignIsNotActive_whenSinceIsNotSet(): void {
        $this->store->stubCampaignActiveRange('campaign', null, '1970');
        $this->assertFalse($this->presenter->campaignActive('campaign'));
    }

    #[Test]
    public function campaignIsNotActive_whenUntilIsNotSet(): void {
        $this->store->stubCampaignActiveRange('campaign', '1970', null);
        $this->assertFalse($this->presenter->campaignActive('campaign'));
    }
}
