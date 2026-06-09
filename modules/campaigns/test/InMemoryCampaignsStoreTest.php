<?php
namespace Test\Modules\Campaigns;

use Modules\Campaigns\Campaign;
use Modules\Campaigns\CampaignsStore;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(InMemoryCampaignsStore::class)]
class InMemoryCampaignsStoreTest extends TestCase {
    private CampaignsStore $store;

    #[Before]
    public function initialize(): void {
        $this->store = new InMemoryCampaignsStore();
    }

    #[Test]
    public function didNotExistInitially(): void {
        $this->assertNotNull($this->create('new-campaign'));
    }

    #[Test]
    public function existedWhenCreatedDuplicateCampaign(): void {
        $this->create('new-campaign');
        $existed = $this->create('new-campaign');
        $this->assertTrue($existed);
    }

    #[Test]
    public function didNotExistWhenCampaignKeyDiffers(): void {
        $this->create('old-campaign');
        $existed = $this->create('new-campaign');
        $this->assertFalse($existed);
    }

    #[Test]
    public function listCampaigns(): void {
        $this->store->createCampaignReturnId(Campaign::create(
            'key',
            'sidebar',
            'horizontal',
            'redirect',
            null,
            null,
            null));
        [$campaign] = $this->store->listCampaigns();
        $this->assertEquals('key', $campaign->campaignKey);
        $this->assertEquals('redirect', $campaign->redirectUrl);
    }

    private function create(string $campaignKey): bool {
        $createdId = $this->store->createCampaignReturnId(Campaign::create(
            $campaignKey,
            '',
            '',
            '',
            null,
            null,
            null));
        return $createdId === null;
    }
}
