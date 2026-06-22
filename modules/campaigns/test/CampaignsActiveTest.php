<?php
namespace Test\Modules\Campaigns;

use Modules\Campaigns\CampaignPayload;
use Modules\Campaigns\CampaignService;
use Modules\Campaigns\VariantPayload;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(CampaignService::class)]
class CampaignsActiveTest extends TestCase {
    private CampaignService $campaigns;
    private InMemoryCampaignsStore $store;
    private TestCurrentDate $calendar;
    private int|null $lastCampaignId = null;

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
    public function campaignIsNotActive_givenCurrentDate_isBeforeActiveSince(): void {
        $this->calendar->stubCurrentDate('1999-12-31T00:00:00');
        $this->setupCampaignActiveRange('2000-01-01T00:00:00', '2000-01-31T00:00:00');
        $this->assertCampaignNotActive('not-started');
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
        $this->assertCampaignNotActive('finished');
    }

    #[Test]
    public function givenViewsAreBelowTarget_campaignIsActive(): void {
        // given a campaign with target of 2 views
        $this->setupCampaignTargetViews(2);
        // when the campaign is viewed 0 times
        $this->stubCampaignViews(0, 'horizontal');
        // then the campaign is active
        $this->assertCampaignActive();
    }

    #[Test]
    public function givenZeroViews_whenTargetViewsIsNotSet_campaignIsActive(): void {
        // given a campaign without target views
        $this->setupCampaignTargetViews(null);
        // when the campaign is viewed 0 times
        $this->stubCampaignViews(0, 'horizontal');
        // then the campaign is active
        $this->assertCampaignActive();
    }

    #[Test]
    public function givenTwoViews_whenTargetViewsIsNotSet_campaignIsActive(): void {
        // given a campaign without target views
        $this->setupCampaignTargetViews(null);
        // when the campaign is viewed 2 times
        $this->stubCampaignViews(2, 'horizontal');
        // then the campaign is active
        $this->assertCampaignActive();
    }

    #[Test]
    public function givenHorizontalViewsAboveTarget_campaignIsNotActive(): void {
        // given a campaign with target of 2 views
        $this->setupCampaignTargetViews(2);
        // when the campaign is viewed 3 times
        $this->stubCampaignViews(3, 'horizontal');
        // then the campaign is not active
        $this->assertCampaignNotActive('target-reached');
    }

    #[Test]
    public function givenSidebarViewsAboveTarget_campaignIsNotActive(): void {
        // given a campaign with target of 2 views
        $this->setupCampaignTargetViews(2);
        // when the campaign is viewed 3 times
        $this->stubCampaignViews(3, 'sidebar');
        // then the campaign is not active
        $this->assertCampaignNotActive('target-reached');
    }

    #[Test]
    public function givenTotalViewsAboveTarget_campaignIsNotActive(): void {
        // given a campaign with target of 2 views
        $this->setupCampaignTargetViews(7);
        // when the campaign is viewed 3 times
        $this->stubCampaignViews(4, 'sidebar');
        $this->stubCampaignViews(4, 'horizontal');
        // then the campaign is not active
        $this->assertCampaignNotActive('target-reached');
    }

    #[Test]
    public function fullyConfiguredCampaign_isActive(): void {
        $this->setupCampaign(activeSince:false, activeUntil:true, hasTargetViews:true);
        $this->assertCampaignActive();
    }

    #[Test]
    public function campaignWithoutUntilOrTarget_isNotActive(): void {
        $this->setupCampaign(activeSince:false, activeUntil:false, hasTargetViews:false);
        $this->assertCampaignNotActive('misconfigured');
    }

    #[Test]
    public function campaignWithUntilWithoutTarget_isNotActive(): void {
        $this->setupCampaign(activeSince:false, activeUntil:true, hasTargetViews:false);
        $this->assertCampaignActive();
    }

    #[Test]
    public function campaignWithoutUntilWithTarget_isNotActive(): void {
        $this->setupCampaign(activeSince:false, activeUntil:false, hasTargetViews:true);
        $this->assertCampaignActive();
    }

    private function assertCampaignActive(): void {
        $this->assertSame('active', $this->campaigns->campaignStatus($this->lastCampaignId));
    }

    private function assertCampaignNotActive(string $status): void {
        $this->assertSame($status, $this->campaigns->campaignStatus($this->lastCampaignId));
    }

    private function setupCampaign(bool $activeSince, bool $activeUntil, bool $hasTargetViews): void {
        $this->calendar->stubCurrentDate('2000-01-01T00:00:00');
        $this->createCampaign(
            $activeSince ? '1970-01-01T00:00:00' : null,
            $activeUntil ? '2999-12-31T23:59:59' : null,
            $hasTargetViews ? 999 : null);
    }

    private function setupCampaignTargetViews(?int $targetViews): void {
        $this->calendar->stubCurrentDate('2000-01-01T00:00:00');
        $this->createCampaign(
            '1970-01-01T00:00:00',
            '2999-12-31T23:59:59',
            $targetViews);
    }

    private function setupCampaignActiveRange(string $activeSince, string $activeUntil): void {
        $this->createCampaign($activeSince, $activeUntil, 999);
    }

    private function createCampaign(?string $activeSince, ?string $activeUntil, ?int $targetViews): void {
        $this->lastCampaignId = $this->store->createCampaign(new CampaignPayload(
            'campaign-name',
            '',
            $activeSince,
            $activeUntil,
            $targetViews));
        $this->assertNotNull($this->store->createVariant($this->lastCampaignId, new VariantPayload('sidebar', '')));
        $this->assertNotNull($this->store->createVariant($this->lastCampaignId, new VariantPayload('horizontal', '')));
    }

    public function stubCampaignViews(int $views, string $bannerType): void {
        foreach ($this->store->listCampaigns() as $campaign) {
            foreach ($campaign->variants as $variant) {
                if ($bannerType === $variant->payload->bannerType) {
                    foreach (range(1, $views) as $_) {
                        $this->store->viewVariant($variant->id);
                    }
                }
            }
        }
    }
}
