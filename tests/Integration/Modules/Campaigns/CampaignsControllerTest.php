<?php
namespace Tests\Integration\Modules\Campaigns;

use Coyote\Modules\Campaigns\CampaignsController;
use Modules\Campaigns\Store\CampaignPayload;
use Modules\Campaigns\Store\CampaignsStore;
use Modules\Campaigns\Store\VariantPayload;
use Modules\Campaigns\VariantType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Legacy\IntegrationNew\BaseFixture\Server;

#[CoversClass(CampaignsController::class)]
class CampaignsControllerTest extends TestCase {
    use Server\Laravel\Transactional;

    #[Test]
    public function redirectsToHomepage_forNoSuchCampaign(): void {
        $noSuchVariantId = 999_888_777;
        $this->laravel
            ->get("/campaigns/$noSuchVariantId")
            ->assertNotFound();
    }

    #[Test]
    public function redirectToCampaignRedirectUrl(): void {
        $variantId = $this->addCampaign('campaign-key', '/redirect-url');
        $this->laravel
            ->get("/campaigns/$variantId")
            ->assertRedirect('/redirect-url');
    }

    private function addCampaign(string $campaignKey, string $redirectUrl): int {
        $store = $this->instance();
        $campaignId = $store->createCampaign(new CampaignPayload(
            $campaignKey,
            $redirectUrl,
            null,
            null,
            null));
        return $store->createVariant($campaignId, new VariantPayload(VariantType::Horizontal, 'image.png'));
    }

    private function instance(): CampaignsStore {
        return $this->laravel->app->get(CampaignsStore::class);
    }
}
