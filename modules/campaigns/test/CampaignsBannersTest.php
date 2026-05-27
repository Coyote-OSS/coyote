<?php
namespace Test\Modules\Campaigns;

use Modules\Campaigns\CampaignService;
use Modules\Campaigns\DuplicateCampaign;
use Modules\Campaigns\InMemoryCampaignsStore;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(CampaignService::class)]
class CampaignsBannersTest extends TestCase {
    private TestPrivilegedUsers $privilegedUsers;
    private TestRotatingBanners $rotateBanners;
    private CampaignsFacade $facade;

    #[Before]
    public function initialize(): void {
        $this->privilegedUsers = new TestPrivilegedUsers();
        $this->rotateBanners = new TestRotatingBanners();
        $this->facade = new CampaignsFacade(new CampaignService(
            $this->privilegedUsers,
            $this->rotateBanners,
            new TestCurrentDate('2000-01-01T00:00:00'),
            new InMemoryCampaignsStore()));
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
        $this->facade->addCampaign(campaignKey:'first');
        $this->facade->addCampaign(campaignKey:'second');
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
        $this->facade->addCampaign(horizontalBanner:'foo.png', campaignKey:'key-1');
        $this->facade->addCampaign(horizontalBanner:'bar.png', campaignKey:'key-2');
        $this->assertEquals(['foo.png', 'bar.png'], $this->facade->getHorizontalBannerUrls());
    }

    #[Test]
    public function sidebarBannerRotates(): void {
        $this->facade->addCampaign(sidebarBanner:'first.png', campaignKey:'key-1');
        $this->facade->addCampaign(sidebarBanner:'second.png', campaignKey:'key-2');
        $this->assertEquals('first.png', $this->facade->getSidebarBannerUrl());
        $this->rotateBanners->rotate();
        $this->assertEquals('second.png', $this->facade->getSidebarBannerUrl());
    }

    #[Test]
    public function failToCreateCampaignWithDuplicateKey(): void {
        $this->facade->addCampaign(redirectUrl:'duplicate-key');
        $this->expectException(DuplicateCampaign::class);
        $this->expectExceptionMessage('Failed to add a duplicated campaign.');
        $this->facade->addCampaign(redirectUrl:'duplicate-key');
    }

    #[Test]
    public function sidebarCampaignKeyForRedirectUrl(): void {
        $this->facade->addCampaign(campaignKey:'campaignKey');
        $this->assertEquals('campaignKey', $this->facade->getSidebarCampaignKey());
    }

    #[Test]
    public function sidebarCampaignKeyRotates(): void {
        $this->facade->addCampaign(campaignKey:'first');
        $this->facade->addCampaign(campaignKey:'second');
        $this->assertEquals('first', $this->facade->getSidebarCampaignKey());
        $this->rotateBanners->rotate();
        $this->assertEquals('second', $this->facade->getSidebarCampaignKey());
    }

    #[Test]
    public function givenThreeCampaigns_firstTwoAreAvailable(): void {
        $this->facade->addCampaign(campaignKey:'first');
        $this->facade->addCampaign(campaignKey:'second');
        $this->facade->addCampaign(campaignKey:'third');
        $this->assertSame(['first', 'second'], $this->facade->getHorizontalCampaignKeys());
    }

    #[Test]
    public function givenThreeCampaigns_afterRotation_lastTwoAreAvailable(): void {
        $this->facade->addCampaign(campaignKey:'first');
        $this->facade->addCampaign(campaignKey:'second');
        $this->facade->addCampaign(campaignKey:'third');
        $this->rotateBanners->rotate();
        $this->assertSame(['second', 'third'], $this->facade->getHorizontalCampaignKeys());
    }

    #[Test]
    public function horizontalBannerContainsRedirectUrl(): void {
        $this->facade->addCampaign(campaignKey:'first-key');
        $this->facade->addCampaign(campaignKey:'second-key');
        $this->assertEquals(['first-key', 'second-key'],
            $this->facade->getHorizontalCampaignKeys());
    }

    #[Test]
    public function sidebarBannerType(): void {
        $this->facade->addCampaign();
        $this->assertEquals('sidebar', $this->facade->sidebarBanner()->bannerType);
    }

    #[Test]
    public function horizontalBannerType(): void {
        $this->facade->addCampaign();
        $this->assertEquals('horizontal', $this->facade->horizontalBanners()[0]->bannerType);
    }

    private function assertArrayKeys(array $expectedKeys, array $actualArray): void {
        $this->assertEquals($expectedKeys, \array_keys($actualArray));
    }
}
