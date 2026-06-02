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
        $this->store->createIfNotExists(
            'key',
            'sidebar',
            'horizontal',
            'redirect',
            '2222-02-02T22:22:22',
            '3333-03-03T03:33:33',
            999);
        [$campaign] = $this->store->listCampaigns();
        $this->assertEquals('key', $campaign->campaignKey);
        $this->assertEquals('sidebar', $campaign->sidebarBanner);
        $this->assertEquals('horizontal', $campaign->horizontalBanner);
        $this->assertEquals('redirect', $campaign->redirectUrl);
        $this->assertEquals('2222-02-02 22:22:22', $campaign->activeSince);
        $this->assertEquals('3333-03-03 03:33:33', $campaign->activeUntil);
        $this->assertEquals(999, $campaign->targetViews);
    }

    #[Test]
    public function findCampaign_returnsCampaignObject(): void {
        $this->store->createIfNotExists(
            'campaign-key',
            'sidebar',
            'horizontal',
            'redirect',
            '2222-02-02T22:22:22',
            '3333-03-03T03:33:33',
            999);
        $campaign = $this->store->findCampaign('campaign-key');
        $this->assertEquals('campaign-key', $campaign->campaignKey);
        $this->assertEquals('sidebar', $campaign->sidebarBanner);
        $this->assertEquals('horizontal', $campaign->horizontalBanner);
        $this->assertEquals('redirect', $campaign->redirectUrl);
        $this->assertEquals('2222-02-02 22:22:22', $campaign->activeSince);
        $this->assertEquals('3333-03-03 03:33:33', $campaign->activeUntil);
        $this->assertEquals(999, $campaign->targetViews);
    }

    #[Test]
    public function findCampaign_returnsNull_ifNoSuchCampaign(): void {
        $this->assertNull($this->store->findCampaign('no-such-campaign'));
    }

    #[Test]
    public function allowOptionalTargetViews(): void {
        $this->store->createIfNotExists('', '', '', '', null, null, null);
        [$campaign] = $this->store->listCampaigns();
        $this->assertNull($campaign->targetViews);
    }

    #[Test]
    public function failToCountCampaignClicks_noSuchCampaign(): void {
        $this->expectException(Campaigns\NoSuchCampaign::class);
        $this->expectExceptionMessage('No such campaign.');
        $this->store->campaignClickCount('key', 'banner');
    }

    #[Test]
    public function countCampaignClicks_campaignHasNoClicks(): void {
        $this->createCampaign('key');
        $this->assertEquals(0, $this->store->campaignClickCount('key', 'banner'));
    }

    #[Test]
    public function countCampaignClicks_campaignHasOneClick(): void {
        $this->createCampaign('key');
        $this->store->campaignClick('key', 'banner');
        $this->assertEquals(1, $this->store->campaignClickCount('key', 'banner'));
    }

    #[Test]
    public function countCampaignClicks_campaignHasManyClicks_sameBanner(): void {
        $this->createCampaign('key');
        $this->store->campaignClick('key', 'banner');
        $this->store->campaignClick('key', 'banner');
        $this->assertEquals(2, $this->store->campaignClickCount('key', 'banner'));
    }

    #[Test]
    public function countCampaignClicks_campaignHasManyClicks_differentBanners(): void {
        $this->createCampaign('key');
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
        $this->createCampaign('key');
        $this->assertEquals(0, $this->store->campaignViewCount('key', 'banner'));
    }

    #[Test]
    public function countCampaignViews_campaignHasOneView(): void {
        $this->createCampaign('key');
        $this->store->campaignView('key', 'banner');
        $this->assertEquals(1, $this->store->campaignViewCount('key', 'banner'));
    }

    #[Test]
    public function countCampaignViews_campaignHasManyView(): void {
        $this->createCampaign('key');
        $this->store->campaignView('key', 'banner');
        $this->store->campaignView('key', 'banner');
        $this->assertEquals(2, $this->store->campaignViewCount('key', 'banner'));
    }

    #[Test]
    public function campaignActiveRange(): void {
        $this->createCampaignRange('key', '1970-01-01T00:00:00', '2222-02-22T22:22:22');
        [$since, $unitl] = $this->store->campaignActiveRange('key');
        $this->assertSame('1970-01-01 00:00:00', $since);
        $this->assertSame('2222-02-22 22:22:22', $unitl);
    }

    #[Test]
    public function campaignActiveRange_failForNoSuchCampaign(): void {
        $this->expectException(Campaigns\NoSuchCampaign::class);
        $this->expectExceptionMessage('No such campaign.');
        $this->store->campaignActiveRange('missing');
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

    private function createCampaign(string $campaignKey): void {
        $this->assertFalse($this->createIfNotExists($campaignKey));
    }

    private function createIfNotExists(string $campaignKey): bool {
        $date = '1970-01-01T00:00:00';
        return $this->store->createIfNotExists($campaignKey, '', '', '', $date, $date, 999);
    }

    private function createCampaignRange(
        string $campaignKey,
        string $activeSince,
        string $activeUntil,
    ): void {
        $this->store->createIfNotExists(
            $campaignKey,
            '', '', '',
            $activeSince,
            $activeUntil,
            999);
    }

    #[Test]
    public function createCampaignWithCampaignKey(): void {
        $this->store->createCampaignReturnId($this->campaign('inserted-campaign'));
        $this->laravel->assertSeeInDatabase('module_campaigns', ['campaign_key' => 'inserted-campaign']);
    }

    #[Test]
    public function reportCreatedSuccessfully(): void {
        $createdId = $this->store->createCampaignReturnId($this->campaign('new-campaign'));
        $this->assertNotNull($createdId);
    }

    #[Test]
    public function reportNotCreatedForDuplicateKey(): void {
        $this->insert('existing-campaign');
        $createdId = $this->store->createCampaignReturnId($this->campaign('existing-campaign'));
        $this->assertNull($createdId);
    }

    #[Test]
    public function returnCreatedCampaignId(): void {
        $campaignId = $this->store->createCampaignReturnId($this->campaign('inserted-campaign'));
        $this->laravel->assertSeeInDatabase('module_campaigns', [
            'id'           => $campaignId,
            'campaign_key' => 'inserted-campaign',
        ]);
    }

    #[Test]
    public function createCampaignWithFields(): void {
        $this->store->createCampaignReturnId(new Campaigns\Campaign(
            'campaign-key',
            'sidebar-banner',
            'horizontal-banner',
            'redirect-url',
            '2000-01-01',
            '2000-01-01',
            20,
        ));
        $this->laravel->assertSeeInDatabase('module_campaigns', [
            'campaign_key' => 'campaign-key',
            'sidebar'      => 'sidebar-banner',
            'horizontal'   => 'horizontal-banner',
            'redirect_url' => 'redirect-url',
            'active_since' => '2000-01-01',
            'active_until' => '2000-01-01',
            'target_views' => 20,
        ]);
    }

    #[Test]
    public function createCampaignWithOptionalFields(): void {
        $this->store->createCampaignReturnId(new Campaigns\Campaign(
            'campaign-key',
            'sidebar-banner',
            'horizontal-banner',
            'redirect-url',
            null,
            null,
            null,
        ));
        $this->laravel->assertSeeInDatabase('module_campaigns', [
            'campaign_key' => 'campaign-key',
            'active_since' => null,
            'active_until' => null,
            'target_views' => null,
        ]);
    }

    private function campaign(string $campaignKey): Campaigns\Campaign {
        return $this->campaignRedirectUrl($campaignKey, '');
    }

    private function campaignRedirectUrl(string $campaignKey, string $redirectUrl): Campaigns\Campaign {
        return new Campaigns\Campaign($campaignKey, '', '', $redirectUrl, null, null, null);
    }

    #[Test]
    public function updateCampaignFields(): void {
        // given
        $campaignId = $this->store->createCampaignReturnId(new Campaigns\Campaign(
            'old-key',
            'old-sidebar',
            'old-horizontal',
            'old-redirect-url',
            '1970-01-01',
            '1970-01-01',
            5,
        ));
        // when
        $this->store->updateCampaign($campaignId, new Campaigns\Campaign(
            'new-key',
            'new-sidebar',
            'new-horizontal',
            'new-redirect-url',
            '2011-11-11',
            '2012-12-12',
            66));
        // then
        $this->laravel->assertSeeInDatabase('module_campaigns', [
            'campaign_key' => 'old-key',
            'sidebar'      => 'new-sidebar',
            'horizontal'   => 'new-horizontal',
            'redirect_url' => 'new-redirect-url',
            'active_since' => '2011-11-11 00:00:00',
            'active_until' => '2012-12-12 00:00:00',
            'target_views' => 66,
        ]);
        $this->laravel->assertDatabaseRecordNotExists('module_campaigns', [
            'campaign_key' => 'new-key',
        ]);
    }

    #[Test]
    public function reportUpdatedSuccessfully(): void {
        // given
        $campaignId = $this->store->createCampaignReturnId($this->campaign('key'));
        // when
        $updated = $this->store->updateCampaign($campaignId, $this->campaign('key'));
        // then
        $this->assertTrue($updated);
    }

    #[Test]
    public function reportNotUpdatedForNoSuchCampaign(): void {
        // when
        $noSuchCampaignId = -1;
        $updated = $this->store->updateCampaign($noSuchCampaignId, $this->campaign('key'));
        // then
        $this->assertFalse($updated);
    }

    #[Test]
    public function updateCampaignByCampaignId(): void {
        // given
        $firstId = $this->store->createCampaignReturnId($this->campaignRedirectUrl('first-campaign', 'original-url'));
        $secondId = $this->store->createCampaignReturnId($this->campaignRedirectUrl('second-campaign', 'original-url'));
        // when
        $this->store->updateCampaign($secondId, $this->campaignRedirectUrl('', 'updated-url'));
        // then
        $this->laravel->assertSeeInDatabase('module_campaigns', [
            'campaign_key' => 'first-campaign',
            'redirect_url' => 'original-url',
        ]);
    }
}
