<?php
namespace Tests\Integration\Modules\Campaigns;

use Coyote\Modules\Campaigns\DatabaseCampaignsStore;
use Illuminate\Database;
use Illuminate\Database\Connection;
use Illuminate\Database\Query;
use Modules\Campaigns;
use Modules\Campaigns\CampaignsStore;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Legacy\IntegrationNew\BaseFixture\Server;

#[CoversClass(DatabaseCampaignsStore::class)]
class DatabaseCampaignsStoreTest extends TestCase {
    use Server\Laravel\Transactional;

    private CampaignsStore $store;
    private Database\Connection $connection;

    #[Before]
    public function initialize(): void {
        $this->connection = $this->laravel->app->get(Connection::class);
        $this->store = new DatabaseCampaignsStore($this->connection);
        $this->table()->delete();
    }

    #[Test]
    public function didNotExistInitially(): void {
        $this->assertFalse($this->createIfNotExists('new-campaign'));
    }

    #[Test]
    public function createdCampaign(): void {
        $this->createIfNotExists('new-campaign');
        $this->laravel->assertSeeInDatabase('module_campaigns', ['campaign_key' => 'new-campaign']);
    }

    #[Test]
    public function existedWhenCreatedDuplicateCampaign(): void {
        $this->insert('new-campaign');
        $this->assertTrue($this->createIfNotExists('new-campaign'));
    }

    #[Test]
    public function doesNotInsertIfAlreadyExists(): void {
        $this->insert('new-campaign');
        $this->createIfNotExists('old-campaign');
        $this->assertEquals(1, $this->selectCount('new-campaign'));
    }

    #[Test]
    public function didNotExistWhenCampaignKeyDiffers(): void {
        $this->insert('other-campaign');
        $existed = $this->createIfNotExists('new-campaign');
        $this->assertFalse($existed);
    }

    #[Test]
    public function listCampaigns(): void {
        $this->store->createIfNotExists('key', 'sidebar', 'horizontal', 'redirect');
        [$campaign] = $this->store->listCampaigns();
        $this->assertEquals('key', $campaign->campaignKey);
        $this->assertEquals('sidebar', $campaign->sidebarBanner);
        $this->assertEquals('horizontal', $campaign->horizontalBanner);
        $this->assertEquals('redirect', $campaign->redirectUrl);
    }

    #[Test]
    public function failToCountCampaignClicks_noSuchCampaign(): void {
        $this->expectException(Campaigns\NoSuchCampaign::class);
        $this->expectExceptionMessage('No such campaign.');
        $this->store->campaignClickCount('key', 'banner');
    }

    #[Test]
    public function countCampaignClicks_campaignHasNoClicks(): void {
        $this->store->createIfNotExists('key', '', '', '');
        $this->assertEquals(0, $this->store->campaignClickCount('key', 'banner'));
    }

    #[Test]
    public function countCampaignClicks_campaignHasOneClick(): void {
        $this->store->createIfNotExists('key', '', '', '');
        $this->store->campaignClick('key', 'banner');
        $this->assertEquals(1, $this->store->campaignClickCount('key', 'banner'));
    }

    #[Test]
    public function countCampaignClicks_campaignHasManyClicks_sameBanner(): void {
        $this->store->createIfNotExists('key', '', '', '');
        $this->store->campaignClick('key', 'banner');
        $this->store->campaignClick('key', 'banner');
        $this->assertEquals(2, $this->store->campaignClickCount('key', 'banner'));
    }

    #[Test]
    public function countCampaignClicks_campaignHasManyClicks_differentBanners(): void {
        $this->store->createIfNotExists('key', '', '', '');
        $this->store->campaignClick('key', 'banner1');
        $this->store->campaignClick('key', 'banner2');
        $this->store->campaignClick('key', 'banner2');
        $this->assertEquals(1, $this->store->campaignClickCount('key', 'banner1'));
        $this->assertEquals(2, $this->store->campaignClickCount('key', 'banner2'));
    }

    #[Test]
    public function failToClick_noSuchCampaign(): void {
        $this->expectException(Campaigns\NoSuchCampaign::class);
        $this->expectExceptionMessage('No such campaign.');
        $this->store->campaignClick('key', 'banner');
    }

    #[Test]
    public function failToCountCampaignViews_noSuchCampaign(): void {
        $this->expectException(Campaigns\NoSuchCampaign::class);
        $this->expectExceptionMessage('No such campaign.');
        $this->store->campaignView('key', 'banner');
    }

    #[Test]
    public function countCampaignViews_campaignHasZeroViews(): void {
        $this->store->createIfNotExists('key', '', '', '');
        $this->assertEquals(0, $this->store->campaignViewCount('key', 'banner'));
    }

    #[Test]
    public function countCampaignViews_campaignHasOneView(): void {
        $this->store->createIfNotExists('key', '', '', '');
        $this->store->campaignView('key', 'banner');
        $this->assertEquals(1, $this->store->campaignViewCount('key', 'banner'));
    }

    #[Test]
    public function countCampaignViews_campaignHasManyView(): void {
        $this->store->createIfNotExists('key', '', '', '');
        $this->store->campaignView('key', 'banner');
        $this->store->campaignView('key', 'banner');
        $this->assertEquals(2, $this->store->campaignViewCount('key', 'banner'));
    }

    private function createIfNotExists(string $campaignKey): bool {
        return $this->store->createIfNotExists($campaignKey, '', '', '');
    }

    private function insert(string $campaignKey): void {
        $this->table()->insert([
            'campaign_key' => $campaignKey,
            'sidebar'      => '',
            'horizontal'   => '',
            'redirect_url' => '',
        ]);
    }

    private function selectCount(string $campaignKey): int {
        return $this->table()->where(['campaign_key' => $campaignKey])->count();
    }

    private function table(): Query\Builder {
        return $this->connection->table('module_campaigns');
    }
}
