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

    #[Before]
    public function initialize(): void {
        $this->store = new InMemoryCampaignsStore();
        $this->calendar = new TestCurrentDate();
        $this->campaigns = new CampaignService(
            new TestPrivilegedUsers(),
            new TestRotatingBanners(),
            $this->calendar,
            $this->store);
    }

    #[Test]
    public function campaignIsNotActive_whenSinceIsNotSet(): void {
        $this->addCampaign('campaign', null, '1970');
        $this->assertFalse($this->campaigns->isCampaignActive('campaign'));
    }

    #[Test]
    public function campaignIsNotActive_whenUntilIsNotSet(): void {
        $this->addCampaign('campaign', '1970', null);
        $this->assertFalse($this->campaigns->isCampaignActive('campaign'));
    }

    #[Test]
    public function campaignIsNotActive_givenCurrentDate_isBeforeActiveSince(): void {
        $this->stubCurrentDate('1999-12-31T00:00:00');
        $this->addCampaign('campaign', '2000-01-01T00:00:00', '2000-01-31T00:00:00');
        $this->assertFalse($this->campaigns->isCampaignActive('campaign'));
    }

    #[Test]
    public function campaignIsActive_givenCurrentDate_isAfterActiveSince_andBeforeActiveUntil(): void {
        $this->stubCurrentDate('2000-01-15T00:00:00');
        $this->addCampaign('campaign', '2000-01-01T00:00:00', '2000-01-31T00:00:00');
        $this->assertTrue($this->campaigns->isCampaignActive('campaign'));
    }

    #[Test]
    public function campaignIsNotActive_givenCurrentDate_isAfterActiveUntil(): void {
        $this->stubCurrentDate('2000-02-01T00:00:00');
        $this->addCampaign('campaign', '2000-01-01T00:00:00', '2000-01-31T00:00:00');
        $this->assertFalse($this->campaigns->isCampaignActive('campaign'));
    }

    #[Test]
    public function givenViewsAreBelowTarget_campaignIsActive(): void {
        // given a campaign with target of 2 views
        $this->setupCampaignTargetViews('campaign', 2);
        // when the campaign is viewed 0 times
        $this->store->stubCampaignViews(0, 'horizontal');
        // then the campaign is active
        $this->assertTrue($this->campaigns->isCampaignActive('campaign'));
    }

    #[Test]
    public function givenHorizontalViewsAboveTarget_campaignIsNotActive(): void {
        // given a campaign with target of 2 views
        $this->setupCampaignTargetViews('campaign', 2);
        // when the campaign is viewed 3 times
        $this->store->stubCampaignViews(3, 'horizontal');
        // then the campaign is not active
        $this->assertFalse($this->campaigns->isCampaignActive('campaign'));
    }

    #[Test]
    public function givenSidebarViewsAboveTarget_campaignIsNotActive(): void {
        // given a campaign with target of 2 views
        $this->setupCampaignTargetViews('campaign', 2);
        // when the campaign is viewed 3 times
        $this->store->stubCampaignViews(3, 'sidebar');
        // then the campaign is not active
        $this->assertFalse($this->campaigns->isCampaignActive('campaign'));
    }

    #[Test]
    public function givenTotalViewsAboveTarget_campaignIsNotActive(): void {
        // given a campaign with target of 2 views
        $this->setupCampaignTargetViews('campaign', 7);
        // when the campaign is viewed 3 times
        $this->store->stubCampaignViews(4, 'sidebar');
        $this->store->stubCampaignViews(4, 'horizontal');
        // then the campaign is not active
        $this->assertFalse($this->campaigns->isCampaignActive('campaign'));
    }

    #[Test]
    public function givenTargetNotSet_campaignIsNotActive(): void {
        // given a campaign without target views
        $this->setupCampaignTargetViews('campaign', null);
        // then the campaign is not active
        $this->assertFalse($this->campaigns->isCampaignActive('campaign'));
    }

    private function stubCurrentDate(string $currentDate): void {
        $this->calendar->stubCurrentDate($currentDate);
    }

    private function addCampaign(
        string  $campaignKey,
        ?string $activeSince,
        ?string $activeUntil,
    ): void {
        $this->campaigns->add('', '', $campaignKey, '', $activeSince, $activeUntil, 999);
    }

    private function setupCampaignTargetViews(string $campaignKey, ?int $targetViews): void {
        $this->stubCurrentDate('2000-01-15T00:00:00');
        $this->campaigns->add(
            '',
            '',
            $campaignKey,
            '',
            '1970-01-01T00:00:00',
            '2999-12-31T23:59:59',
            $targetViews);
    }
}
