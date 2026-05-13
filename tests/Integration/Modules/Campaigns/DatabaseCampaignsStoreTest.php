<?php
namespace Tests\Integration\Modules\Campaigns;

use Coyote\Modules\Campaigns\DatabaseCampaignsStore;
use Illuminate\Database\Connection;
use Illuminate\Database\Query;
use Illuminate\Support\Facades\DB;
use Modules\Campaigns\CampaignsStore;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Legacy\IntegrationNew\BaseFixture;
use Tests\Legacy\IntegrationNew\BaseFixture\Server\Laravel;

#[CoversClass(DatabaseCampaignsStore::class)]
class DatabaseCampaignsStoreTest extends TestCase {
    use BaseFixture\Server\Http;
    use Laravel\Transactional;

    private CampaignsStore $store;

    #[Before]
    public function initialize(): void {
        $connection = $this->laravel->app->get(Connection::class);
        $this->store = new DatabaseCampaignsStore($connection);
    }

    #[Test]
    public function didNotExistInitially(): void {
        $this->assertFalse($this->store->createIfNotExists('new-campaign'));
    }

    #[Test]
    public function createdCampaign(): void {
        $this->store->createIfNotExists('new-campaign');
        $this->laravel->assertSeeInDatabase('campaign_keys', ['key' => 'new-campaign']);
    }

    #[Test]
    public function existedWhenCreatedDuplicateCampaign(): void {
        $this->table()->insert(['key' => 'new-campaign']);
        $this->assertTrue($this->store->createIfNotExists('new-campaign'));
    }

    #[Test]
    public function doesNotInsertIfAlreadyExists(): void {
        $this->table()->insert(['key' => 'new-campaign']);
        $this->store->createIfNotExists('old-campaign');
        $this->assertEquals(1, $this->table()->where('key', 'new-campaign')->count());
    }

    #[Test]
    public function didNotExistWhenCampaignKeyDiffers(): void {
        $this->table()->insert(['key' => 'other-campaign']);
        $existed = $this->store->createIfNotExists('new-campaign');
        $this->assertFalse($existed);
    }

    private function table(): Query\Builder {
        return DB::table('campaign_keys');
    }
}
