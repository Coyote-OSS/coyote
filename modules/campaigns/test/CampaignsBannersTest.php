<?php
namespace Test\Modules\Campaigns;

use Libs\Arrays\arrays;
use Modules\Campaigns\CampaignService;
use Modules\Campaigns\VariantType;
use Modules\Campaigns\Voivodeship;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Test\Modules\Campaigns\Fixture\CampaignsFacade;
use Test\Modules\Campaigns\Fixture\TestCurrentDate;
use Test\Modules\Campaigns\Fixture\TestPrivilegedUsers;
use Test\Modules\Campaigns\Fixture\TestRotatingBanners;
use Test\Modules\Campaigns\Fixture\TestUserVoivodeship;
use Test\Modules\Campaigns\Store\InMemoryCampaignsStore;

#[CoversClass(CampaignService::class)]
class CampaignsBannersTest extends TestCase {
    private TestPrivilegedUsers $privilegedUsers;
    private TestRotatingBanners $rotateBanners;
    /** @deprecated */
    private CampaignsFacade $facade;
    private TestCurrentDate $date;
    private TestUserVoivodeship $userVoivodeship;
    private CampaignService $campaigns;

    #[Before]
    public function initialize(): void {
        $this->privilegedUsers = new TestPrivilegedUsers();
        $this->rotateBanners = new TestRotatingBanners();
        $this->date = new TestCurrentDate('2000-01-01T00:00:00');
        $this->userVoivodeship = new TestUserVoivodeship();
        $store = new InMemoryCampaignsStore();
        $this->campaigns = new CampaignService(
            $this->privilegedUsers,
            $this->rotateBanners,
            $this->date,
            $store,
            $this->userVoivodeship);
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
    public function noBanner_forPriviligedUser_dueToBeingRobot(): void {
        $this->facade->addCampaign(sidebarBanner:'sidebar.png', horizontalBanner:'horizontal.png');
        $this->privilegedUsers->setUserRobot(true);
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
    public function premiumCampaign_withLeaderBoardXl_showsLeaderBoardXlInsteadOfHorizontal(): void {
        $campaignId = $this->facade->createCampaign(isPremium:true);
        $this->facade->createVariant($campaignId, 'standard.png', VariantType::Standard);
        $this->facade->createVariant($campaignId, 'leaderboard-xl.png', VariantType::LeaderBoardXl);
        $this->assertEquals(['leaderboard-xl.png'], $this->facade->getHorizontalBannerUrls());
    }

    #[Test]
    public function premiumCampaign_withoutLeaderBoardXl_showsHorizontalAsUsual(): void {
        $campaignId = $this->facade->createCampaign(isPremium:true);
        $this->facade->createVariant($campaignId, 'standard.png', VariantType::Standard);
        $this->assertEquals(['standard.png'], $this->facade->getHorizontalBannerUrls());
    }

    #[Test]
    public function nonPremiumCampaign_withLeaderBoardXl_showsHorizontalAsUsual(): void {
        $campaignId = $this->facade->createCampaign(isPremium:false);
        $this->facade->createVariant($campaignId, 'standard.png', VariantType::Standard);
        $this->facade->createVariant($campaignId, 'leaderboard-xl.png', VariantType::LeaderBoardXl);
        $this->assertEquals(['standard.png'], $this->facade->getHorizontalBannerUrls());
    }

    #[Test]
    public function premiumCampaign_withLeaderBoard_showsHorizontalAsUsual(): void {
        $campaignId = $this->facade->createCampaign(isPremium:true);
        $this->facade->createVariant($campaignId, 'standard.png', VariantType::Standard);
        $this->facade->createVariant($campaignId, 'leaderboard.png', VariantType::LeaderBoard);
        $this->assertEquals(['standard.png'], $this->facade->getHorizontalBannerUrls());
    }

    #[Test]
    public function soleNonPremiumCampaign_withLeaderBoard_showsLeaderBoardInsteadOfHorizontal(): void {
        $campaignId = $this->facade->createCampaign(isPremium:false);
        $this->facade->createVariant($campaignId, 'standard.png', VariantType::Standard);
        $this->facade->createVariant($campaignId, 'leaderboard.png', VariantType::LeaderBoard);
        $this->assertEquals(['leaderboard.png'], $this->facade->getHorizontalBannerUrls());
    }

    #[Test]
    public function nonSoleNonPremiumCampaign_withLeaderBoard_showsHorizontalAsUsual(): void {
        $campaignId = $this->facade->createCampaign(isPremium:false);
        $this->facade->createVariant($campaignId, 'standard.png', VariantType::Standard);
        $this->facade->createVariant($campaignId, 'leaderboard.png', VariantType::LeaderBoard);
        $otherId = $this->facade->createCampaign();
        $this->facade->createVariant($otherId, 'other.png', VariantType::Standard);
        $this->assertEquals(['standard.png', 'other.png'], $this->facade->getHorizontalBannerUrls());
    }

    #[Test]
    public function premiumCampaign_withBothLeaderBoardVariants_prefersLeaderBoardXl(): void {
        $campaignId = $this->facade->createCampaign(isPremium:true);
        $this->facade->createVariant($campaignId, 'standard.png', VariantType::Standard);
        $this->facade->createVariant($campaignId, 'leaderboard.png', VariantType::LeaderBoard);
        $this->facade->createVariant($campaignId, 'leaderboard-xl.png', VariantType::LeaderBoardXl);
        $this->assertEquals(['leaderboard-xl.png'], $this->facade->getHorizontalBannerUrls());
    }

    #[Test]
    public function leaderBoardXlPicked_excludesTheOtherHorizontalBanner(): void {
        $leaderBoardId = $this->facade->createCampaign(isPremium:true);
        $this->facade->createVariant($leaderBoardId, 'standard.png', VariantType::Standard);
        $this->facade->createVariant($leaderBoardId, 'leaderboard-xl.png', VariantType::LeaderBoardXl);
        $otherId = $this->facade->createCampaign();
        $this->facade->createVariant($otherId, 'other.png', VariantType::Standard);
        $this->assertEquals(['leaderboard-xl.png'], $this->facade->getHorizontalBannerUrls());
    }

    #[Test]
    public function standardCampaign_whenItsTurnComesBeforeLeaderBoard_isShownAloneAndLeaderBoardIsSkipped(): void {
        $otherId = $this->facade->createCampaign();
        $this->facade->createVariant($otherId, 'other.png', VariantType::Standard);
        $leaderBoardId = $this->facade->createCampaign(isPremium:true);
        $this->facade->createVariant($leaderBoardId, 'standard.png', VariantType::Standard);
        $this->facade->createVariant($leaderBoardId, 'leaderboard-xl.png', VariantType::LeaderBoardXl);
        $this->assertEquals(['other.png'], $this->facade->getHorizontalBannerUrls());
    }

    #[Test]
    public function leaderBoardCampaign_doesNotPermanentlyStarveOutOthers_theyTakeTurnsAcrossRotations(): void {
        $leaderBoardId = $this->facade->createCampaign(name:'leader', isPremium:true);
        $this->facade->createVariant($leaderBoardId, 'standard.png', VariantType::Standard);
        $this->facade->createVariant($leaderBoardId, 'leaderboard-xl.png', VariantType::LeaderBoardXl);
        $otherId = $this->facade->createCampaign(name:'other');
        $this->facade->createVariant($otherId, 'other.png', VariantType::Standard);

        $this->assertEquals(['leaderboard-xl.png'], $this->facade->getHorizontalBannerUrls());
        $this->rotateBanners->rotate();
        $this->assertEquals(['other.png'], $this->facade->getHorizontalBannerUrls());
    }

    #[Test]
    public function doNotIncludeInactiveCampaigns(): void {
        $this->date->stubCurrentDate('2000-01-02');
        $inactiveId = $this->facade->addCampaign(name:'inactive', since:'2100-01-01', until:'2100-01-01');
        $activeId = $this->facade->addCampaign(name:'active', since:'2000-01-01', until:'2000-01-03');
        $campaignBanners = $this->campaigns->campaignBanners()->horizontal;
        $this->assertCampaignKeys(["$activeId"], $campaignBanners);
    }

    #[Test]
    public function variantIsEnabledByDefault(): void {
        $campaignId = $this->facade->createCampaign();
        $this->facade->createVariant($campaignId, 'enabled-by-default.png', VariantType::Standard);
        $this->assertEquals(['enabled-by-default.png'], $this->facade->getHorizontalBannerUrls());
    }

    #[Test]
    public function disabledVariant_isNeverPicked(): void {
        $campaignId = $this->facade->createCampaign();
        $this->facade->createVariant($campaignId, 'disabled.png', VariantType::Standard, enabled:false);
        $this->assertEquals([], $this->facade->getHorizontalBannerUrls());
    }

    #[Test]
    public function givenEnabledAndDisabledVariant_disabledOneIsNeverPicked(): void {
        $campaignId = $this->facade->createCampaign();
        $this->facade->createVariant($campaignId, 'enabled.png', VariantType::Standard, enabled:true);
        $this->facade->createVariant($campaignId, 'disabled.png', VariantType::Standard, enabled:false);
        for ($i = 0; $i < 5; $i++) {
            $this->assertEquals(['enabled.png'], $this->facade->getHorizontalBannerUrls());
            $this->rotateBanners->rotate();
        }
    }

    #[Test]
    public function campaignWithoutVoivodeship_isShown_regardlessOfUserVoivodeship(): void {
        $this->userVoivodeship->stubVoivodeship(null);
        $this->facade->addCampaign(horizontalBanner:'unrestricted.png');
        $this->assertEquals(['unrestricted.png'], $this->facade->getHorizontalBannerUrls());
    }

    #[Test]
    public function campaignWithVoivodeship_isShown_whenUserVoivodeshipMatches(): void {
        $this->userVoivodeship->stubVoivodeship(Voivodeship::Mazowieckie);
        $campaignId = $this->facade->createCampaign(voivodeship:Voivodeship::Mazowieckie);
        $this->facade->createVariant($campaignId, 'matching.png', VariantType::Standard);
        $this->assertEquals(['matching.png'], $this->facade->getHorizontalBannerUrls());
    }

    #[Test]
    public function campaignWithVoivodeship_isHidden_whenUserVoivodeshipDoesNotMatch(): void {
        $this->userVoivodeship->stubVoivodeship(Voivodeship::Slaskie);
        $campaignId = $this->facade->createCampaign(voivodeship:Voivodeship::Mazowieckie);
        $this->facade->createVariant($campaignId, 'non-matching.png', VariantType::Standard);
        $this->assertEmpty($this->facade->getHorizontalBannerUrls());
    }

    #[Test]
    public function campaignWithVoivodeship_isHidden_whenUserVoivodeshipIsUnknown(): void {
        $this->userVoivodeship->stubVoivodeship(null);
        $campaignId = $this->facade->createCampaign(voivodeship:Voivodeship::Mazowieckie);
        $this->facade->createVariant($campaignId, 'restricted.png', VariantType::Standard);
        $this->assertEmpty($this->facade->getHorizontalBannerUrls());
    }

    private function assertCampaignKeys(
        array $expectedCampaignKeys,
        array $actualCampaignBanners,
    ): void {
        $this->assertSame(
            $expectedCampaignKeys,
            $actualCampaignBanners |> arrays::map(fn($banner) => $banner->campaignKey));
    }

    private function assertArrayKeys(array $expectedKeys, array $actualArray): void {
        $this->assertEquals($expectedKeys, \array_keys($actualArray));
    }
}
