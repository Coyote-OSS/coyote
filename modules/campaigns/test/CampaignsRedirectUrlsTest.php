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

    #[Before]
    public function initialize(): void {
        $this->campaigns = new CampaignService(
            new TestPrivilegedUsers(),
            new TestRotatingBanners(),
            new InMemoryCampaignsStore());
    }

    #[Test]
    public function redirectUrl(): void {
        $this->campaigns->add('', '', 'campaign', 'http://redirect-url');
        $redirectUrl = $this->campaigns->redirectUrl('campaign');
        $this->assertEquals('http://redirect-url', $redirectUrl);
    }

    #[Test]
    public function sidebarCampaignKey(): void {
        $this->campaigns->add('', '', 'campaignKey', '');
        $this->assertEquals('campaignKey',
            $this->campaigns->campaignBanners()->sidebar->campaignKey);
    }

    #[Test]
    public function missingCampaign(): void {
        $this->expectException(NoSuchCampaign::class);
        $this->expectExceptionMessage('Failed');
        $this->campaigns->redirectUrl('missing');
    }

    // TODO hide after time passes
    // TODO hide after views passed
    // TODO count and present views
    // TODO count and present clicks
    // TODO present ctr
}
