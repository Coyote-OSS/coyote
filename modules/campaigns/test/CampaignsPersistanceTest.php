<?php
namespace Test\Modules\Campaigns;

use Modules\Campaigns\Campaign;
use Modules\Campaigns\CampaignService;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(CampaignService::class)]
class CampaignsPersistanceTest extends TestCase {
    private CampaignService $campaigns;
    private InMemoryCampaignsStore $store;

    #[Before]
    public function initialize(): void {
        $this->store = new InMemoryCampaignsStore();
        $this->campaigns = new CampaignService(
            new TestPrivilegedUsers(),
            new TestRotatingBanners(),
            new TestCurrentDate('2000-01-02T00:00:00'),
            $this->store);
    }

    #[Test]
    public function banners(): void {
        $this->store->createCampaignReturnId(Campaign::create(
            'stored',
            'sidebar',
            'horizontal',
            '',
            '2000-01-01T00:00:00',
            '2000-01-03T00:00:00',
            999));
        $banners = $this->campaigns->campaignBanners();
        $this->assertEquals('stored', $banners->sidebar->campaignKey);
        $this->assertEquals('sidebar', $banners->sidebar->bannerUrl);
        $this->assertEquals('horizontal', $banners->horizontal[0]->bannerUrl);
    }

    #[Test]
    public function redirectUrl(): void {
        $this->setupCampaignRedirectUrl('stored', 'redirect');
        $this->assertEquals('redirect', $this->campaigns->redirectUrl('stored'));
    }

    private function setupCampaignRedirectUrl(string $campaignKey, string $redirectUrl): void {
        $this->store->createCampaignReturnId(Campaign::create(
            $campaignKey,
            '',
            '',
            $redirectUrl,
            null,
            null,
            null));
    }
}
