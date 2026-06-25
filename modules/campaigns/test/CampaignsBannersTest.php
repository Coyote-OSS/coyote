<?php
namespace Test\Modules\Campaigns;

use Modules\Campaigns\CampaignService;
use Modules\Campaigns\VariantType;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Test\Modules\Campaigns\Store\InMemoryCampaignsStore;

#[CoversClass(CampaignService::class)]
class CampaignsBannersTest extends TestCase {
    private TestPrivilegedUsers $privilegedUsers;
    private TestRotatingBanners $rotateBanners;
    /** @deprecated */
    private CampaignsFacade $facade;
    private TestCurrentDate $date;
    private CampaignService $campaigns;

    #[Before]
    public function initialize(): void {
        $this->privilegedUsers = new TestPrivilegedUsers();
        $this->rotateBanners = new TestRotatingBanners();
        $this->date = new TestCurrentDate('2000-01-01T00:00:00');
        $store = new InMemoryCampaignsStore();
        $this->campaigns = new CampaignService(
            $this->privilegedUsers,
            $this->rotateBanners,
            $this->date,
            $store);
        $this->facade = new CampaignsFacade($this->campaigns, $store);
    }

    #[Test]
    public function noSidebarBanner(): void {
        $this->assertNull($this->facade->getSidebarBannerUrl());
    }

    #[Test]
    public function noHorizontalBanners(): void {
        $this->assertEmpty($this->facade->getHorizontalBannerUrls());
    }

    #[Test]
    public function noSidebarCampaignKey(): void {
        $this->assertNull($this->facade->getSidebarCampaignKey());
    }

    #[Test]
    public function singleSidebarBanner(): void {
        $this->facade->addCampaign(sidebarBanner:'sidebar.png');
        $this->assertEquals('sidebar.png', $this->facade->getSidebarBannerUrl());
    }

    #[Test]
    public function singleHorizontalBanner(): void {
        $this->facade->addCampaign(horizontalBanner:'horizontal.png');
        $this->assertEquals(['horizontal.png'], $this->facade->getHorizontalBannerUrls());
    }

    #[Test]
    public function horizontalBannersAreSequential(): void {
        $this->facade->addCampaign(name:'first');
        $this->facade->addCampaign(name:'second');
        $this->assertArrayKeys([0, 1], $this->facade->getHorizontalBannerUrls());;
    }

    #[Test]
    public function noBanner_forPriviligedUser_dueToHighReputation(): void {
        $this->facade->addCampaign(sidebarBanner:'sidebar.png', horizontalBanner:'horizontal.png');
        $this->privilegedUsers->setUserHighReputation(true);
        $this->assertNull($this->facade->getSidebarBannerUrl());
        $this->assertEmpty($this->facade->getHorizontalBannerUrls());
        $this->assertNull($this->facade->getSidebarCampaignKey());
    }

    #[Test]
    public function noBanner_forPriviligedUser_dueToBeingSponsor(): void {
        $this->facade->addCampaign(sidebarBanner:'sidebar.png', horizontalBanner:'horizontal.png');
        $this->privilegedUsers->setUserSponsor(true);
        $this->assertNull($this->facade->getSidebarBannerUrl());
        $this->assertEmpty($this->facade->getHorizontalBannerUrls());
        $this->assertNull($this->facade->getSidebarCampaignKey());
    }

    #[Test]
    public function twoHorizontalBanners(): void {
        $this->facade->addCampaign(horizontalBanner:'foo.png', name:'key-1');
        $this->facade->addCampaign(horizontalBanner:'bar.png', name:'key-2');
        $this->assertEquals(['foo.png', 'bar.png'], $this->facade->getHorizontalBannerUrls());
    }

    #[Test]
    public function sidebarBannerRotates(): void {
        $this->facade->addCampaign(sidebarBanner:'first.png', name:'key-1');
        $this->facade->addCampaign(sidebarBanner:'second.png', name:'key-2');
        $this->assertEquals('first.png', $this->facade->getSidebarBannerUrl());
        $this->rotateBanners->rotate();
        $this->assertEquals('second.png', $this->facade->getSidebarBannerUrl());
    }

    #[Test]
    public function sidebarCampaignKeyForRedirectUrl(): void {
        $campaignId = $this->facade->addCampaign(name:'campaignKey');
        $this->assertEquals($campaignId, $this->facade->getSidebarCampaignKey());
    }

    #[Test]
    public function sidebarCampaignKeyRotates(): void {
        $first = $this->facade->addCampaign(name:'first');
        $second = $this->facade->addCampaign(name:'second');
        $this->assertEquals($first, $this->facade->getSidebarCampaignKey());
        $this->rotateBanners->rotate();
        $this->assertEquals($second, $this->facade->getSidebarCampaignKey());
    }

    #[Test]
    public function givenThreeCampaigns_firstTwoAreAvailable(): void {
        $first = $this->facade->addCampaign(name:'first');
        $second = $this->facade->addCampaign(name:'second');
        $third = $this->facade->addCampaign(name:'third');
        $this->assertSame(["$first", "$second"], $this->facade->getHorizontalCampaignKeys());
    }

    #[Test]
    public function givenThreeCampaigns_afterRotation_lastTwoAreAvailable(): void {
        $first = $this->facade->addCampaign(name:'first');
        $second = $this->facade->addCampaign(name:'second');
        $third = $this->facade->addCampaign(name:'third');
        $this->rotateBanners->rotate();
        $this->assertSame(["$second", "$third"], $this->facade->getHorizontalCampaignKeys());
    }

    #[Test]
    public function horizontalBannerContainsRedirectUrl(): void {
        $first = $this->facade->addCampaign(name:'first-key');
        $second = $this->facade->addCampaign(name:'second-key');
        $this->assertEquals(["$first", "$second"],
            $this->facade->getHorizontalCampaignKeys());
    }

    #[Test]
    public function sidebarBannerType(): void {
        $this->facade->addCampaign();
        $this->assertEquals(VariantType::Sidebar, $this->facade->sidebarBanner()->type);
    }

    #[Test]
    public function horizontalBannerType(): void {
        $this->facade->addCampaign();
        $this->assertEquals(VariantType::Standard, $this->facade->horizontalBanners()[0]->type);
    }

    #[Test]
    public function givenCampaign_withThreeVariants_oneVariantIsAvailable(): void {
        $campaignId = $this->facade->createCampaign();
        $this->facade->createVariant($campaignId, 'first.png', VariantType::Standard);
        $this->facade->createVariant($campaignId, 'second.png', VariantType::Standard);
        $this->facade->createVariant($campaignId, 'third.png', VariantType::Standard);
        $this->assertSame(['first.png'], $this->facade->getHorizontalBannerUrls());
    }

    #[Test]
    public function doNotIncludeInactiveCampaigns(): void {
        $this->date->stubCurrentDate('2000-01-02');
        $inactiveId = $this->facade->addCampaign(name:'inactive', since:'2100-01-01', until:'2100-01-01');
        $activeId = $this->facade->addCampaign(name:'active', since:'2000-01-01', until:'2000-01-03');
        $campaignBanners = $this->campaigns->campaignBanners()->horizontal;
        $this->assertCampaignKeys(["$activeId"], $campaignBanners);
    }

    private function assertCampaignKeys(
        array $expectedCampaignKeys,
        array $actualCampaignBanners,
    ): void {
        $this->assertSame(
            $expectedCampaignKeys,
            array_map(fn($banner) => $banner->campaignKey, $actualCampaignBanners));
    }

    private function assertArrayKeys(array $expectedKeys, array $actualArray): void {
        $this->assertEquals($expectedKeys, \array_keys($actualArray));
    }
}
