<?php
namespace Tests\Integration\Modules\Campaigns\Eloquent;

use Coyote\Modules\Campaigns\Eloquent\EloquentCampaignsStore;
use Illuminate\Database;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use Modules\Campaigns;
use Modules\Campaigns\Store\CampaignsStore;
use Modules\Campaigns\Store\VariantPayload;
use Modules\Campaigns\VariantType;
use Modules\Campaigns\Voivodeship;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Test\Modules\Campaigns\Store\CampaignStoreContractTests;
use Tests\Legacy\IntegrationNew\BaseFixture\Server;

#[CoversClass(EloquentCampaignsStore::class)]
class EloquentCampaignsStoreTest extends TestCase {
    use Server\Laravel\Transactional;
    use CampaignStoreContractTests;

    private CampaignsStore $store;
    private Database\Connection $connection;

    #[Before(20)]
    public function initialize(): void {
        $this->connection = $this->laravel->app->get(Connection::class);
        $this->store = new EloquentCampaignsStore();
        $this->table()->delete();
    }

    #[Test]
    public function insertsCampaignWithPayload(): void {
        $this->store->createCampaign(new Campaigns\Store\CampaignPayload(
            'campaign-name', 'redirect-url', '2011-01-01', '2022-02-02',
            42, 'campaign-description', true,
            Voivodeship::Pomorskie,
        ));
        $this->laravel->assertSeeInDatabase('module_campaigns', [
            'name'         => 'campaign-name',
            'redirect_url' => 'redirect-url',
            'active_since' => '2011-01-01',
            'active_until' => '2022-02-02',
            'target_views' => 42,
            'description'  => 'campaign-description',
            'is_premium'   => true,
            'voivodeship'  => 'pomorskie',
        ]);
    }

    #[Test]
    public function insertsAndFindsCampaignWithVoivodeship(): void {
        $campaignId = $this->store->createCampaign(new Campaigns\Store\CampaignPayload(
            'campaign-name', 'redirect-url', null, null, null, null, false, Voivodeship::Lodzkie,
        ));
        $this->laravel->assertSeeInDatabase('module_campaigns', [
            'id'          => $campaignId,
            'voivodeship' => 'łódzkie',
        ]);
        Assert::assertSame(Voivodeship::Lodzkie, $this->store->findCampaign($campaignId)->payload->voivodeship);
    }

    #[Test]
    public function insertsCampaignVariantWithPayload(): void {
        $campaignId = $this->createCampaign();
        $variantId = $this->store->createVariant($campaignId, new VariantPayload(
            VariantType::Standard,
            'image-url',
        ));
        $this->laravel->assertSeeInDatabase('module_campaign_variants', [
            'id'          => $variantId,
            'campaign_id' => $campaignId,
            'image_url'   => 'image-url',
            'type'        => 'horizontal',
        ]);
    }

    protected function contractTestStore(): CampaignsStore {
        return $this->store;
    }

    private function table(): Builder {
        return $this->connection->table('module_campaigns');
    }
}
