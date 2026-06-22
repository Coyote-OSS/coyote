<?php
namespace Test\Modules\Campaigns;

use Modules\Campaigns\CampaignService;
use Modules\Campaigns\NoSuchCampaign;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(CampaignService::class)]
class CampaignsRedirectUrlsTest extends TestCase {
    private CampaignService $campaigns;
    private TestCurrentDate $date;
    private CampaignsFacade $facade;

    #[Before]
    public function initialize(): void {
        $this->date = new TestCurrentDate();
        $store = new InMemoryCampaignsStore();
        $this->campaigns = new CampaignService(
            new TestPrivilegedUsers(),
            new TestRotatingBanners(),
            $this->date,
            $store);
        $this->facade = new CampaignsFacade($this->campaigns, $store);
    }

    #[Test]
    public function redirectUrl(): void {
        $campaignId = $this->facade->addCampaign(campaignKey:'campaign', redirectUrl:'http://redirect-url');
        $redirectUrl = $this->campaigns->redirectUrl($campaignId);
        $this->assertEquals('http://redirect-url', $redirectUrl);
    }

    #[Test]
    public function sidebarCampaignKey(): void {
        $this->date->stubCurrentDate('2000-01-02');
        $campaignId = $this->facade->addCampaign(campaignKey:'campaignKey', since:'2000-01-01', until:'2000-01-03');
        $this->assertEquals("$campaignId",
            $this->campaigns->campaignBanners()->sidebar->campaignKey);
    }

    #[Test]
    public function missingCampaign(): void {
        $this->expectException(NoSuchCampaign::class);
        $this->expectExceptionMessage('Failed to get campaign redirect url.');
        $noSuchCampaignId = 999;
        $this->campaigns->redirectUrl($noSuchCampaignId);
    }

    #[Test]
    public function doNotIncludeInactiveCampaigns(): void {
        $this->date->stubCurrentDate('2000-01-02');
        $inactiveId = $this->facade->addCampaign(campaignKey:'inactive', since:'2100-01-01', until:'2100-01-01');
        $activeId = $this->facade->addCampaign(campaignKey:'active', since:'2000-01-01', until:'2000-01-03');
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
}
