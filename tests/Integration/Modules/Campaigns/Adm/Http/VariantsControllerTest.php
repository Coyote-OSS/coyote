<?php
namespace Adm\Http;

use Coyote\Modules\Campaigns\Adm;
use Coyote\Modules\Campaigns\DatabaseCampaignsStore;
use Illuminate\Testing\TestResponse;
use Modules\Campaigns\Campaign;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Legacy\IntegrationNew\BaseFixture\Forum\ModelsDriver;
use Tests\Legacy\IntegrationNew\BaseFixture\Server;

#[CoversClass(Adm\Http\VariantsController::class)]
class VariantsControllerTest extends TestCase {
    use Server\Laravel\Transactional;
    use Server\RelativeUri;

    private ModelsDriver $models;

    #[Before]
    public function givenAccessToCampaigns(): void {
        $this->models = new ModelsDriver();
        $this->loginAdmin();
    }

    #[Test]
    public function creatingVariant_failsWithoutAuthorization(): void {
        // given I don't have access to variants
        $this->loginUser();
        // when I attempt to create a new variant
        $response = $this->httpCreate(1, []);
        // then the request is rejected
        $response->assertForbidden();
    }

    #[Test]
    public function failToCreateVariant_withNoSuchCampaigns(): void {
        // when I attempt to create a variant in a non-existent campaign
        $response = $this->httpCreate(9999, $this->exampleVariant());
        // then the request is rejected
        $response->assertUnprocessable();
    }

    #[Test]
    public function creatingVariant_savesVariantInDatabase(): void {
        // given campaign exists
        $campaignId = $this->createCampaign();
        // when I create a new variant
        $this->httpCreate($campaignId, $this->exampleVariant());
        // then the variant is persisted
        $this->laravel->assertSeeInDatabase('module_campaign_variants', [
            'campaign_id' => $campaignId,
        ]);
    }

    #[Test]
    public function createVariant_withImageUrl(): void {
        $campaignId = $this->createCampaign();
        // when I create a variant with type horizontal
        $this->httpCreate($campaignId, $this->exampleVariant(imageUrl:'http://banner.png'));
        // then the variant type is persisted
        $this->laravel->assertSeeInDatabase('module_campaign_variants', [
            'campaign_id' => $campaignId,
            'image_url'   => 'http://banner.png',
        ]);
    }

    #[Test]
    public function failToCreateVariant_withEmptyImageUrl(): void {
        // when I create a new variant without image
        $response = $this->httpCreate($this->createCampaign(), $this->exampleVariantNoImage());
        // then the request is rejected
        $response->assertSessionHasErrors([
            'image_url' => 'Grafika baneru jest wymagana.',
        ]);
    }

    #[Test]
    public function createVariant_withTypeHorizontal(): void {
        $campaignId = $this->createCampaign();
        // when I create a variant with type horizontal
        $this->httpCreate($campaignId, $this->exampleVariant(type:'horizontal'));
        // then the variant type is persisted
        $this->laravel->assertSeeInDatabase('module_campaign_variants', [
            'campaign_id' => $campaignId,
            'type'        => 'horizontal',
        ]);
    }

    #[Test]
    public function createVariant_withTypeSidebar(): void {
        // when I create a variant with type sidebar
        $this->httpCreate($this->createCampaign(), $this->exampleVariant(type:'sidebar'));
        // then the variant type is persisted
        $this->laravel->assertSeeInDatabase('module_campaign_variants', [
            'type' => 'sidebar',
        ]);
    }

    #[Test]
    public function failToCreateVariant_withInvalidType(): void {
        // when I create a variant with an invalid type
        $response = $this->httpCreate($this->createCampaign(), $this->exampleVariant(type:'invalid'));
        // then the request is rejected
        $response->assertSessionHasErrors([
            'type' => 'The selected type is invalid.',
        ]);
    }

    #[Test]
    public function failToCreateVariant_withoutType(): void {
        // when I create a new variant without type
        $response = $this->httpCreate($this->createCampaign(), $this->exampleVariantNoType());
        // then the request is rejected
        $response->assertSessionHasErrors([
            'type' => 'Pole type jest wymagane.',
        ]);
    }

    #[Test]
    public function creatingVariant_redirectsToCampaignView(): void {
        $campaignId = $this->createCampaign();
        // when the variant is created
        $response = $this->httpCreate($campaignId, $this->exampleVariant());
        // then the response redirects to campaign view
        $response->assertRedirectToRoute('adm.campaigns.show', [$campaignId]);
    }

    #[Test]
    public function routeAliasCreate(): void {
        $this->assertRelativeUri('/Adm/Campaigns/12/Variants/Save', route('adm.campaigns.variants.save', [12]));
    }

    private function assertRelativeUri(string $expected, string $actual): void {
        $this->assertSame('http://nginx' . $expected, $actual);
    }

    private function httpCreate(int $campaignId, array $variant): TestResponse {
        return $this->laravel->post("/Adm/Campaigns/$campaignId/Variants/Save", $variant);
    }

    private function loginUser(): void {
        $this->server->loginById($this->models->newUserReturnId());
    }

    private function loginAdmin(): void {
        $this->server->loginById($this->models->newUserReturnId(permissionName:'adm-access'));
        $this->laravel->withSession(['admin' => true]);
    }

    private function createCampaign(): int {
        /** @var DatabaseCampaignsStore $store */
        $store = $this->laravel->app->make(DatabaseCampaignsStore::class);
        return $store->createCampaignReturnId(Campaign::create(
            'campaign',
            '', '', '',
            null, null, null,
        ));
    }

    private function exampleVariant(
        ?string $imageUrl = null,
        ?string $type = null,
    ): array {
        return [
            'image_url' => $imageUrl ?? 'http://image.png',
            'type'      => $type ?? 'horizontal',
        ];
    }

    private function exampleVariantNoImage(): array {
        return [
            ...$this->exampleVariant(),
            'image_url' => '',
        ];
    }

    private function exampleVariantNoType(): array {
        return [
            ...$this->exampleVariant(),
            'type' => null,
        ];
    }
}
