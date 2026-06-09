<?php
namespace Tests\Integration\Modules\Campaigns\Adm\View;

use Coyote\Modules\Campaigns\Adm\View\CampaignPresenter;
use Modules\Campaigns\Campaign;
use Modules\Campaigns\CampaignService;
use Modules\Campaigns\CampaignVariant;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Test\Modules\Campaigns\InMemoryCampaignsStore;
use Test\Modules\Campaigns\TestCurrentDate;
use Test\Modules\Campaigns\TestPrivilegedUsers;
use Test\Modules\Campaigns\TestRotatingBanners;

#[CoversClass(CampaignPresenter::class)]
class CampaignPresenterTest extends TestCase {
    private InMemoryCampaignsStore $store;
    private TestCurrentDate $date;
    private CampaignPresenter $presenter;

    #[Before]
    public function beforeEach(): void {
        $this->store = new InMemoryCampaignsStore();
        $this->date = new TestCurrentDate();
        $this->presenter = new CampaignPresenter($this->store, new CampaignService(
            new TestPrivilegedUsers(),
            new TestRotatingBanners(),
            $this->date,
            $this->store));
    }

    #[Test]
    public function horizontalViewModel_clicks(): void {
        $campaignId = $this->stubCampaign('foo');
        $this->store->stubCampaignClicks(13, 'horizontal');
        $campaignPresenter = $this->presenter;
        [$horizontal] = $campaignPresenter->bannerViewModelsById($campaignId);
        $this->assertEquals(-1, $horizontal->stats->clicks);
    }

    #[Test]
    public function horizontalViewModel_views(): void {
        $campaignId = $this->stubCampaign('foo');
        $this->store->stubCampaignViews(12, 'horizontal');
        $campaignPresenter = $this->presenter;
        [$horizontal, $sidebar] = $campaignPresenter->bannerViewModelsById($campaignId);
        $this->assertEquals(-1, $horizontal->stats->views);
    }

    #[Test]
    public function campaignViews_isSumOfBannerViews(): void {
        $this->store->stubCampaignViews(12, 'horizontal');
        $this->store->stubCampaignViews(13, 'sidebar');
        $vm = $this->presenter->campaignStats('foo');
        $this->assertEquals(25, $vm->views);
    }

    #[Test]
    public function bannerViewModelTypes(): void {
        $campaignId = $this->stubCampaignBannerUrls('foo', [
            new CampaignVariant('', 'sidebar'),
            new CampaignVariant('', 'horizontal'),
        ]);
        [$sidebar, $horizontal] = $this->presenter->bannerViewModelsById($campaignId);
        $this->assertSame('sidebar', $sidebar->type);
        $this->assertSame('horizontal', $horizontal->type);
    }

    #[Test]
    public function bannerImageUrl(): void {
        $campaignId = $this->stubCampaignBannerUrls('foo', [
            new CampaignVariant('side.png', 'sidebar'),
        ]);
        [$sidebar] = $this->presenter->bannerViewModelsById($campaignId);
        $this->assertSame('side.png', $sidebar->imageUrl);
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
        $this->assertTrue($this->presenter->campaignStatus('campaign')->active());
    }

    private function stubCampaign(
        string  $campaignKey,
        ?string $sidebarUrl = null,
        ?string $horizontalUrl = null,
    ): int {
        return $this->stubCampaignBannerUrls($campaignKey, [
            new CampaignVariant($sidebarUrl ?? 'not-set', 'sidebar'),
            new CampaignVariant($horizontalUrl ?? 'not-set', 'horizontal'),
        ]);
    }

    /**
     * @param CampaignVariant[] $variants
     */
    private function stubCampaignBannerUrls(string $campaignKey, array $variants): int {
        return $this->store->createCampaignReturnId(new Campaign(
            $campaignKey,
            '',
            null, null, null,
            $variants));
    }

    private function stubCampaignActiveRange(
        string  $campaignKey,
        ?string $activeSince,
        ?string $activeUntil,
    ): void {
        $this->store->createCampaignReturnId(Campaign::create(
            $campaignKey,
            '',
            '',
            '',
            $activeSince,
            $activeUntil,
            999));
    }
}
