<?php
namespace Test\Modules\Campaigns;

use Modules\Campaigns\CampaignService;
use Modules\Campaigns\InMemoryCampaignsStore;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(CampaignService::class)]
class CampaignsActiveTest extends TestCase {
    private CampaignService $campaigns;
    private InMemoryCampaignsStore $store;
    private TestCurrentDate $calendar;
    private string $campaignKey;

    #[Before]
    public function initialize(): void {
        $this->store = new InMemoryCampaignsStore();
        $this->calendar = new TestCurrentDate();
        $this->campaigns = new CampaignService(
            new TestPrivilegedUsers(),
            new TestRotatingBanners(),
            $this->calendar,
            $this->store);
        $this->campaignKey = 'campaign-key';
    }

    #[Test]
    public function campaignIsNotActive_givenCurrentDate_isBeforeActiveSince(): void {
        $this->calendar->stubCurrentDate('1999-12-31T00:00:00');
        $this->setupCampaignActiveRange('2000-01-01T00:00:00', '2000-01-31T00:00:00');
        $this->assertCampaignNotActive();
    }

    #[Test]
    public function campaignIsActive_givenCurrentDate_isAfterActiveSince_andBeforeActiveUntil(): void {
        $this->calendar->stubCurrentDate('2000-01-15T00:00:00');
        $this->setupCampaignActiveRange('2000-01-01T00:00:00', '2000-01-31T00:00:00');
        $this->assertCampaignActive();
    }

    #[Test]
    public function campaignIsNotActive_givenCurrentDate_isAfterActiveUntil(): void {
        $this->calendar->stubCurrentDate('2000-02-01T00:00:00');
        $this->setupCampaignActiveRange('2000-01-01T00:00:00', '2000-01-31T00:00:00');
        $this->assertCampaignNotActive();
    }

    #[Test]
    public function givenViewsAreBelowTarget_campaignIsActive(): void {
        // given a campaign with target of 2 views
        $this->setupCampaignTargetViews(2);
        // when the campaign is viewed 0 times
        $this->store->stubCampaignViews(0, 'horizontal');
        // then the campaign is active
        $this->assertCampaignActive();
    }

    #[Test]
    public function givenZeroViews_whenTargetViewsIsNotSet_campaignIsActive(): void {
        // given a campaign without target views
        $this->setupCampaignTargetViews(null);
        // when the campaign is viewed 0 times
        $this->store->stubCampaignViews(0, 'horizontal');
        // then the campaign is active
        $this->assertCampaignActive();
    }

    #[Test]
    public function givenTwoViews_whenTargetViewsIsNotSet_campaignIsActive(): void {
        // given a campaign without target views
        $this->setupCampaignTargetViews(null);
        // when the campaign is viewed 2 times
        $this->store->stubCampaignViews(2, 'horizontal');
        // then the campaign is active
        $this->assertCampaignActive();
    }

    #[Test]
    public function givenHorizontalViewsAboveTarget_campaignIsNotActive(): void {
        // given a campaign with target of 2 views
        $this->setupCampaignTargetViews(2);
        // when the campaign is viewed 3 times
        $this->store->stubCampaignViews(3, 'horizontal');
        // then the campaign is not active
        $this->assertCampaignNotActive();
    }

    #[Test]
    public function givenSidebarViewsAboveTarget_campaignIsNotActive(): void {
        // given a campaign with target of 2 views
        $this->setupCampaignTargetViews(2);
        // when the campaign is viewed 3 times
        $this->store->stubCampaignViews(3, 'sidebar');
        // then the campaign is not active
        $this->assertCampaignNotActive();
    }

    #[Test]
    public function givenTotalViewsAboveTarget_campaignIsNotActive(): void {
        // given a campaign with target of 2 views
        $this->setupCampaignTargetViews(7);
        // when the campaign is viewed 3 times
        $this->store->stubCampaignViews(4, 'sidebar');
        $this->store->stubCampaignViews(4, 'horizontal');
        // then the campaign is not active
        $this->assertCampaignNotActive();
    }

    #[Test]
    public function fullyConfiguredCampaign_isActive(): void {
        $this->setupCampaign(since:false, until:true, target:true);
        $this->assertCampaignActive();
    }

    #[Test]
    public function campaignWithoutUntilOrTarget_isNotActive(): void {
        $this->setupCampaign(since:false, until:false, target:false);
        $this->assertCampaignNotActive();
    }

    #[Test]
    public function campaignWithUntilWithoutTarget_isNotActive(): void {
        $this->setupCampaign(since:false, until:true, target:false);
        $this->assertCampaignActive();
    }

    #[Test]
    public function campaignWithoutUntilWithTarget_isNotActive(): void {
        $this->setupCampaign(since:false, until:false, target:true);
        $this->assertCampaignActive();
    }

    private function assertCampaignActive(): void {
        $this->assertTrue($this->campaigns->isCampaignActive($this->campaignKey));
    }

    private function assertCampaignNotActive(): void {
        $this->assertFalse($this->campaigns->isCampaignActive($this->campaignKey));
    }

    private function setupCampaign(bool $since, bool $until, bool $target): void {
        $this->calendar->stubCurrentDate('2000-01-01T00:00:00');
        $this->campaigns->add(
            '',
            '',
            $this->campaignKey,
            '',
            $since ? '1970-01-01T00:00:00' : null,
            $until ? '2999-12-31T23:59:59' : null,
            $target ? 999 : null);
    }

    private function setupCampaignActiveRange(string $activeSince, string $activeUntil): void {
        $this->campaigns->add(
            '',
            '',
            $this->campaignKey,
            '',
            $activeSince,
            $activeUntil,
            999);
    }

    private function setupCampaignTargetViews(?int $targetViews) {
        $this->calendar->stubCurrentDate('2000-01-15T00:00:00');
        $this->campaigns->add(
            '',
            '',
            $this->campaignKey,
            '',
            '1970-01-01T00:00:00',
            '2999-12-31T23:59:59',
            $targetViews);
    }
}
