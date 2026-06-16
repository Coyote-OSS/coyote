<?php
namespace Tests\Integration\Modules\Campaigns;

use Coyote\Modules\Campaigns\DatabaseCampaignsStore;
use Illuminate\Database;
use Illuminate\Database\Connection;
use Illuminate\Database\Query;
use Modules\Campaigns;
use Modules\Campaigns\Campaign;
use Modules\Campaigns\CampaignsStore;
use Modules\Campaigns\CampaignVariant;
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
        $this->assertFalse($this->createCampaign('new-campaign'));
    }

    #[Test]
    public function createdCampaign(): void {
        $this->createCampaign('new-campaign');
        $this->laravel->assertSeeInDatabase('module_campaigns', ['campaign_key' => 'new-campaign']);
    }

    #[Test]
    public function existedWhenCreatedDuplicateCampaign(): void {
        $this->insert('new-campaign');
        $this->assertTrue($this->createCampaign('new-campaign'));
    }

    #[Test]
    public function doesNotInsertIfAlreadyExists(): void {
        $this->insert('new-campaign');
        $this->createCampaign('old-campaign');
        $this->assertEquals(1, $this->selectCount('new-campaign'));
    }

    #[Test]
    public function didNotExistWhenCampaignKeyDiffers(): void {
        $this->insert('other-campaign');
        $existed = $this->createCampaign('new-campaign');
        $this->assertFalse($existed);
    }

    #[Test]
    public function listCampaigns(): void {
        $this->store->createCampaignReturnId(Campaign::create(
            'key',
            'sidebar',
            'horizontal',
            'redirect',
            '2222-02-02T22:22:22',
            '3333-03-03T03:33:33',
            999));
        [$campaign] = $this->store->listCampaigns();
        $this->assertEquals('key', $campaign->campaignKey);
        $this->assertEquals('redirect', $campaign->redirectUrl);
        $this->assertEquals('2222-02-02 22:22:22', $campaign->activeSince);
        $this->assertEquals('3333-03-03 03:33:33', $campaign->activeUntil);
        $this->assertEquals(999, $campaign->targetViews);
    }

    #[Test]
    public function findCampaign_returnsCampaignObject(): void {
        $campaignId = $this->store->createCampaignReturnId(Campaign::create(
            'campaign-key',
            'sidebar',
            'horizontal',
            'redirect',
            '2222-02-02T22:22:22',
            '3333-03-03T03:33:33',
            999));
        $campaign = $this->store->findCampaign($campaignId);
        $this->assertEquals('campaign-key', $campaign->campaignKey);
        $this->assertEquals('redirect', $campaign->redirectUrl);
        $this->assertEquals('2222-02-02 22:22:22', $campaign->activeSince);
        $this->assertEquals('3333-03-03 03:33:33', $campaign->activeUntil);
        $this->assertEquals(999, $campaign->targetViews);
        $this->assertEquals([], $campaign->variants);
    }

    #[Test]
    public function findCampaign_returnsNull_ifNoSuchCampaign(): void {
        $noSuchCampaignId = 9999;
        $this->assertNull($this->store->findCampaign($noSuchCampaignId));
    }

    #[Test]
    public function allowOptionalTargetViews(): void {
        $this->createCampaignTargetViews(null);
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
        $this->givenCampaignExists('key');
        $this->assertEquals(0, $this->store->campaignClickCount('key', 'banner'));
    }

    #[Test]
    public function countCampaignClicks_campaignHasOneClick(): void {
        $this->givenCampaignExists('key');
        $this->store->campaignClick('key', 'banner');
        $this->assertEquals(1, $this->store->campaignClickCount('key', 'banner'));
    }

    #[Test]
    public function countCampaignClicks_campaignHasManyClicks_sameBanner(): void {
        $this->givenCampaignExists('key');
        $this->store->campaignClick('key', 'banner');
        $this->store->campaignClick('key', 'banner');
        $this->assertEquals(2, $this->store->campaignClickCount('key', 'banner'));
    }

    #[Test]
    public function countCampaignClicks_campaignHasManyClicks_differentBanners(): void {
        $this->givenCampaignExists('key');
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
        $this->givenCampaignExists('key');
        $this->assertEquals(0, $this->store->campaignViewCount('key', 'banner'));
    }

    #[Test]
    public function countCampaignViews_campaignHasOneView(): void {
        $this->givenCampaignExists('key');
        $this->store->campaignView('key', 'banner');
        $this->assertEquals(1, $this->store->campaignViewCount('key', 'banner'));
    }

    #[Test]
    public function countCampaignViews_campaignHasManyView(): void {
        $this->givenCampaignExists('key');
        $this->store->campaignView('key', 'banner');
        $this->store->campaignView('key', 'banner');
        $this->assertEquals(2, $this->store->campaignViewCount('key', 'banner'));
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

    private function givenCampaignExists(string $campaignKey): void {
        $this->assertFalse($this->createCampaign($campaignKey));
    }

    private function createCampaignActiveRange(string $campaignKey, string $activeSince, string $activeUntil): void {
        $this->createCampaignReturnId($campaignKey, $activeSince, $activeUntil, 999);
    }

    private function createCampaignTargetViews(?int $targetViews): void {
        $this->createCampaignReturnId('', '1970-01-01T00:00:00', '1970-01-01T00:00:00', $targetViews);
    }

    private function createCampaign(string $campaignKey): bool {
        $createdId = $this->createCampaignReturnId($campaignKey, '1970-01-01T00:00:00', '1970-01-01T00:00:00', 999);
        return $createdId === null;
    }

    private function createCampaignReturnId(string $campaignKey, string $activeSince, string $activeUntil, ?int $targetViews): ?int {
        return $this->store->createCampaignReturnId(Campaign::create(
            $campaignKey,
            '',
            '',
            '',
            $activeSince,
            $activeUntil,
            $targetViews));
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
        $this->store->createCampaignReturnId(Campaigns\Campaign::create(
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
            'sidebar'      => 'not-to-be-used-deprecated',
            'horizontal'   => 'not-to-be-used-deprecated',
            'redirect_url' => 'redirect-url',
            'active_since' => '2000-01-01',
            'active_until' => '2000-01-01',
            'target_views' => 20,
        ]);
    }

    #[Test]
    public function createCampaignWithOptionalFields(): void {
        $this->store->createCampaignReturnId(Campaigns\Campaign::create(
            'campaign-key',
            'sidebar-banner',
            'horizontal-banner',
            'redirect-url',
            null,
            null,
            null));
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
        return Campaigns\Campaign::create($campaignKey, '', '', $redirectUrl, null, null, null);
    }

    #[Test]
    public function updateCampaignFields(): void {
        // given
        $campaignId = $this->store->createCampaignReturnId(Campaigns\Campaign::create(
            'old-key',
            'old-sidebar',
            'old-horizontal',
            'old-redirect-url',
            '1970-01-01',
            '1970-01-01',
            5));
        // when
        $this->store->updateCampaign($campaignId, Campaigns\Campaign::create(
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
            'sidebar'      => 'not-to-be-used-deprecated',
            'horizontal'   => 'not-to-be-used-deprecated',
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

    #[Test]
    public function findCampaignIncludesVariants(): void {
        $campaignId = $this->store->createCampaignReturnId($this->exampleCampaign());
        $this->store->createVariant($campaignId, 'image.png', 'horizontal');
        $campaign = $this->store->findCampaign($campaignId);
        [$variant] = $campaign->variants;
        $this->assertSame('horizontal', $variant->bannerType);
        $this->assertSame('image.png', $variant->bannerUrl);
    }

    #[Test]
    public function findCampaignWithoutVariants(): void {
        $campaignId = $this->store->createCampaignReturnId($this->exampleCampaign());
        $this->assertEmpty($this->store->findCampaign($campaignId)->variants);
    }

    private function exampleCampaign(): Campaign {
        return Campaign::create(
            '', '', '', '', null, null, null,
        );
    }

    #[Test]
    public function storeMultipleVariantsForSidebar(): void {
        $campaignId = $this->store->createCampaignReturnId($this->exampleCampaign());
        $this->store->createVariant($campaignId, 'sidebar1.png', 'sidebar');
        $this->store->createVariant($campaignId, 'sidebar2.png', 'sidebar');
        $campaign = $this->store->findCampaign($campaignId);
        $this->assertEquals([
            new CampaignVariant('sidebar1.png', 'sidebar'),
            new CampaignVariant('sidebar2.png', 'sidebar'),
        ], $campaign->variants);
    }

    #[Test]
    public function storeMultipleVariantsForHorizontal(): void {
        $campaignId = $this->store->createCampaignReturnId($this->exampleCampaign());
        $this->store->createVariant($campaignId, 'horizontal1.png', 'horizontal');
        $this->store->createVariant($campaignId, 'horizontal2.png', 'horizontal');
        $campaign = $this->store->findCampaign($campaignId);
        $this->assertEquals([
            new CampaignVariant('horizontal1.png', 'horizontal'),
            new CampaignVariant('horizontal2.png', 'horizontal'),
        ], $campaign->variants);
    }
}
