<?php
namespace Test\Modules\Campaigns;

use Modules\Campaigns\CampaignService;
use Modules\Campaigns\InMemoryCampaignsStore;
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
            new TestCurrentDate(),
            $this->store);
    }

    #[Test]
    public function banners(): void {
        $this->store->createIfNotExists('stored', 'sidebar', 'horizontal', '', null, null);
        $banners = $this->campaigns->campaignBanners();
        $this->assertEquals('stored', $banners->sidebar->campaignKey);
        $this->assertEquals('sidebar', $banners->sidebar->bannerUrl);
        $this->assertEquals('horizontal', $banners->horizontal[0]->bannerUrl);
    }

    #[Test]
    public function redirectUrl(): void {
        $this->store->createIfNotExists('stored', '', '', 'redirect', null, null);
        $redirectUrl = $this->campaigns->redirectUrl('stored');
        $this->assertEquals('redirect', $redirectUrl);
    }
}
