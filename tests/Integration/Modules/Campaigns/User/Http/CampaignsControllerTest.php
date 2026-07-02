<?php
namespace Tests\Integration\Modules\Campaigns\User\Http;

use Coyote\Modules\Campaigns\User\Http\CampaignsController;
use Modules\Campaigns\Store\CampaignPayload;
use Modules\Campaigns\Store\CampaignsStore;
use Modules\Campaigns\Store\CampaignVariant;
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
    public function clickVariant_returnNotFound_forNoSuchVariant(): void {
        $noSuchVariantId = 999_888_777;
        $this->laravel
            ->get("/campaigns/$noSuchVariantId")
            ->assertNotFound();
    }

    #[Test]
    public function clickVariant_redirect_toRedirectUrl(): void {
        [$_, $variantId] = $this->addCampaignWithVariant('/redirect-url');
        $this->laravel
            ->get("/campaigns/$variantId")
            ->assertRedirect('/redirect-url');
    }

    #[Test]
    public function exposeVariant_returnNotFound_forNoSuchVariant(): void {
        $noSuchVariantId = 999_888_777;
        $this->laravel
            ->post("/campaigns/$noSuchVariantId/expose")
            ->assertNotFound();
    }

    #[Test]
    public function exposeVariant_returnsSuccess(): void {
        [$_, $variantId] = $this->addCampaignWithVariant('/redirect-url');
        $this->laravel
            ->post("/campaigns/$variantId/expose")
            ->assertSuccessful();
    }

    #[Test]
    public function clickVariant_record_variantClick(): void {
        [$campaignId, $variantId] = $this->addCampaignWithVariant();
        $this->laravel->get("/campaigns/$variantId");
        $this->assertSame(1, $this->campaignVariant($campaignId)->clicks);
    }

    #[Test]
    public function exposeVariant_record_variantExposure(): void {
        [$campaignId, $variantId] = $this->addCampaignWithVariant();
        $this->laravel->post("/campaigns/$variantId/expose");
        $this->assertSame(1, $this->campaignVariant($campaignId)->exposures);
    }

    private function addCampaignWithVariant(?string $redirectUrl = null): array {
        $store = $this->instance();
        $campaignId = $store->createCampaign($this->exampleCampaign($redirectUrl));
        $variantId = $store->createVariant($campaignId, new VariantPayload(VariantType::Standard, 'image.png'));
        return [$campaignId, $variantId];
    }

    private function exampleCampaign(?string $redirectUrl): CampaignPayload {
        return new CampaignPayload(null, $redirectUrl ?? '', null, null, null, null);
    }

    private function campaignVariant(mixed $campaignId): CampaignVariant {
        [$variant] = $this->instance()->findCampaign($campaignId)->variants;
        return $variant;
    }

    private function instance(): CampaignsStore {
        return $this->laravel->app->get(CampaignsStore::class);
    }
}
