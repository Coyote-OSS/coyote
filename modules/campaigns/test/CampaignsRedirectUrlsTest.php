<?php
namespace Test\Modules\Campaigns;

use Modules\Campaigns\CampaignService;
use Modules\Campaigns\InMemoryCampaignsStore;
use Modules\Campaigns\NoSuchCampaign;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(CampaignService::class)]
class CampaignsRedirectUrlsTest extends TestCase {
    private CampaignService $campaigns;
    private TestCurrentDate $date;

    #[Before]
    public function initialize(): void {
        $this->date = new TestCurrentDate();
        $this->campaigns = new CampaignService(
            new TestPrivilegedUsers(),
            new TestRotatingBanners(),
            $this->date,
            new InMemoryCampaignsStore());
    }

    #[Test]
    public function redirectUrl(): void {
        $this->campaigns->add('', '', 'campaign', 'http://redirect-url', null, null);
        $redirectUrl = $this->campaigns->redirectUrl('campaign');
        $this->assertEquals('http://redirect-url', $redirectUrl);
    }

    #[Test]
    public function sidebarCampaignKey(): void {
        $this->date->stubCurrentDate('2000-01-02');
        $this->campaigns->add('', '', 'campaignKey', '', '2000-01-01', '2000-01-03');
        $this->assertEquals('campaignKey',
            $this->campaigns->campaignBanners()->sidebar->campaignKey);
    }

    #[Test]
    public function missingCampaign(): void {
        $this->expectException(NoSuchCampaign::class);
        $this->expectExceptionMessage('Failed');
        $this->campaigns->redirectUrl('missing');
    }

    #[Test]
    public function doNotIncludeInactiveCampaigns(): void {
        $this->date->stubCurrentDate('2000-01-02');
        $this->campaigns->add('', '', 'inactive', '', '2100-01-01', '2100-01-01');
        $this->campaigns->add('', '', 'active', '', '2000-01-01', '2000-01-03');
        $campaignBanners = $this->campaigns->campaignBanners()->horizontal;
        $this->assertCampaignKeys(['active'], $campaignBanners);
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
