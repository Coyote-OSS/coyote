<?php
namespace Test\Modules\Campaigns;

use Modules\Campaigns\CampaignService;
use Modules\Campaigns\InMemoryCampaignsStore;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class CampaignsActiveTest extends TestCase {
    private CampaignService $campaigns;
    private InMemoryCampaignsStore $store;

    #[Before]
    public function initialize(): void {
        $this->store = new InMemoryCampaignsStore();
        $this->campaigns = new CampaignService(
            new TestPrivilegedUsers(),
            new TestRotatingBanners(),
            $this->store);
    }

    #[Test]
    public function campaignIsActive_whenBothSinceAndUntilAreSet(): void {
        $this->store->stubCampaignActiveRange('campaign', '1970', '1970');
        $this->assertTrue($this->campaigns->campaignActive('campaign'));
    }

    #[Test]
    public function campaignIsNotActive_whenSinceIsNotSet(): void {
        $this->store->stubCampaignActiveRange('campaign', null, '1970');
        $this->assertFalse($this->campaigns->campaignActive('campaign'));
    }

    #[Test]
    public function campaignIsNotActive_whenUntilIsNotSet(): void {
        $this->store->stubCampaignActiveRange('campaign', '1970', null);
        $this->assertFalse($this->campaigns->campaignActive('campaign'));
    }
}
