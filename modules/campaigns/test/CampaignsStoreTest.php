<?php
namespace Test\Modules\Campaigns;

use Modules\Campaigns\CampaignsStore;
use Modules\Campaigns\InMemoryCampaignsStore;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class CampaignsStoreTest extends TestCase {
    private CampaignsStore $store;

    #[Before]
    public function initialize(): void {
        $this->store = new InMemoryCampaignsStore();
    }

    #[Test]
    public function didNotExistInitially(): void {
        $existed = $this->store->createIfNotExists('new-campaign', '', '', '', null, null, null);
        $this->assertFalse($existed);
    }

    #[Test]
    public function existedWhenCreatedDuplicateCampaign(): void {
        $this->store->createIfNotExists('new-campaign', '', '', '', null, null, null);
        $existed = $this->store->createIfNotExists('new-campaign', '', '', '', null, null, null);
        $this->assertTrue($existed);
    }

    #[Test]
    public function didNotExistWhenCampaignKeyDiffers(): void {
        $this->store->createIfNotExists('old-campaign', '', '', '', null, null, null);
        $existed = $this->store->createIfNotExists('new-campaign', '', '', '', null, null, null);
        $this->assertFalse($existed);
    }

    #[Test]
    public function listCampaigns(): void {
        $this->store->createIfNotExists('key', 'sidebar', 'horizontal', 'redirect', null, null, null);
        [$campaign] = $this->store->listCampaigns();
        $this->assertEquals('key', $campaign->campaignKey);
        $this->assertEquals('sidebar', $campaign->sidebarBanner);
        $this->assertEquals('horizontal', $campaign->horizontalBanner);
        $this->assertEquals('redirect', $campaign->redirectUrl);
    }
}
