<?php
namespace Test\Modules\Campaigns;

use Modules\Campaigns\CampaignService;
use Modules\Campaigns\InMemoryCampaignsStore;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(CampaignService::class)]
class CampaignsActiveTest extends TestCase {
    private CampaignService $campaigns;
    private InMemoryCampaignsStore $store;
    private TestCurrentDate $calendar;

    #[Before]
    public function initialize(): void {
        $this->store = new InMemoryCampaignsStore();
        $this->calendar = new TestCurrentDate();
        $this->campaigns = new CampaignService(
            new TestPrivilegedUsers(),
            new TestRotatingBanners(),
            $this->calendar,
            $this->store);
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

    #[Test]
    public function campaignIsNotActive_givenCurrentDate_isBeforeActiveSince(): void {
        $this->stubCurrentDate('1999-12-31T00:00:00');
        $this->store->stubCampaignActiveRange('campaign', '2000-01-01T00:00:00', '2000-01-31T00:00:00');
        $this->assertFalse($this->campaigns->campaignActive('campaign'));
    }

    #[Test]
    public function campaignIsActive_givenCurrentDate_isAfterActiveSince_andBeforeActiveUntil(): void {
        $this->stubCurrentDate('2000-01-15T00:00:00');
        $this->store->stubCampaignActiveRange('campaign', '2000-01-01T00:00:00', '2000-01-31T00:00:00');
        $this->assertTrue($this->campaigns->campaignActive('campaign'));
    }

    #[Test]
    public function campaignIsNotActive_givenCurrentDate_isAfterActiveUntil(): void {
        $this->stubCurrentDate('2000-02-01T00:00:00');
        $this->store->stubCampaignActiveRange('campaign', '2000-01-01T00:00:00', '2000-01-31T00:00:00');
        $this->assertFalse($this->campaigns->campaignActive('campaign'));
    }

    private function stubCurrentDate(string $currentDate): void {
        $this->calendar->stubCurrentDate($currentDate);
    }
}
