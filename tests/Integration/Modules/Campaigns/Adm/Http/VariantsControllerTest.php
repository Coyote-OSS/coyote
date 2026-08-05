<?php
namespace Tests\Integration\Modules\Campaigns\Adm\Http;

use Coyote\Modules\Campaigns\Adm;
use Coyote\Modules\Campaigns\Eloquent\EloquentCampaignsStore;
use Illuminate\Http\UploadedFile;
use Illuminate\Testing\TestResponse;
use Modules\Campaigns\Store\CampaignPayload;
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
    public function uploadingVariants_failsWithoutAuthorization(): void {
        // given I don't have access to variants
        $this->loginUser();
        // when I attempt to upload a variant
        $response = $this->httpTryUpload($this->createCampaign(), [$this->image(728, 90)]);
        // then the request is rejected
        $response->assertForbidden();
    }

    #[Test]
    public function failToUpload_withNoSuchCampaign(): void {
        // when I attempt to upload a variant to a non-existent campaign
        $response = $this->httpTryUpload(9999, [$this->image(728, 90)]);
        // then the request is rejected
        $response->assertUnprocessable();
    }

    #[Test]
    public function uploadingStandardSizedImage_createsVariantWithDetectedType(): void {
        // given a campaign exists
        $campaignId = $this->createCampaign();
        // when I upload a 728x90 image
        $this->httpUpload($campaignId, [$this->image(728, 90)]);
        // then a "horizontal" (Standard) variant is persisted
        $this->laravel->assertSeeInDatabase('module_campaign_variants', [
            'campaign_id' => $campaignId,
            'type'        => 'horizontal',
        ]);
    }

    #[Test]
    public function uploadingLeaderBoardXlSizedImage_createsVariantWithDetectedType(): void {
        // given a campaign exists
        $campaignId = $this->createCampaign();
        // when I upload a 1140x200 image
        $this->httpUpload($campaignId, [$this->image(1140, 200)]);
        // then a "leaderboard-xl" variant is persisted
        $this->laravel->assertSeeInDatabase('module_campaign_variants', [
            'campaign_id' => $campaignId,
            'type'        => 'leaderboard-xl',
        ]);
    }

    #[Test]
    public function uploadingMultipleImages_createsMultipleVariants(): void {
        // given a campaign exists
        $campaignId = $this->createCampaign();
        // when I upload two differently-sized images in one request
        $this->httpUpload($campaignId, [
            $this->image(728, 90),
            $this->image(300, 250),
        ]);
        // then both variants are persisted with their detected types
        $this->laravel->assertSeeInDatabase('module_campaign_variants', [
            ['campaign_id' => $campaignId, 'type' => 'horizontal'],
            ['campaign_id' => $campaignId, 'type' => 'sidebar'],
        ]);
    }

    #[Test]
    public function uploadingUnsupportedSize_isSkipped_butOtherImagesStillSucceed(): void {
        // given a campaign exists
        $campaignId = $this->createCampaign();
        // when I upload one valid and one unsupported-size image together
        $response = $this->httpUpload($campaignId, [
            $this->image(728, 90),
            $this->image(500, 500),
        ]);
        // then the valid one is persisted
        $this->laravel->assertSeeInDatabase('module_campaign_variants', [
            'campaign_id' => $campaignId,
            'type'        => 'horizontal',
        ]);
        // and a warning about the skipped file is flashed
        $response->assertSessionHas('warning');
    }

    #[Test]
    public function uploadingVariants_redirectsToCampaignView(): void {
        $campaignId = $this->createCampaign();
        // when the variants are uploaded
        $response = $this->httpUpload($campaignId, [$this->image(728, 90)]);
        // then the response redirects to campaign view
        $response->assertRedirectToRoute('adm.campaigns.show', [$campaignId]);
    }

    #[Test]
    public function routeAliasUpload(): void {
        $this->assertRelativeUri('/Adm/Campaigns/12/Variants/Upload', route('adm.campaigns.variants.upload', [12]));
    }

    private function assertRelativeUri(string $expected, string $actual): void {
        $this->assertSame('http://nginx' . $expected, $actual);
    }

    private function httpUpload(int $campaignId, array $images): TestResponse {
        $response = $this->httpTryUpload($campaignId, $images);
        $response->assertRedirect(); // successfully processed
        return $response;
    }

    private function httpTryUpload(int $campaignId, array $images): TestResponse {
        return $this->laravel->post("/Adm/Campaigns/$campaignId/Variants/Upload", [
            'images' => $images,
        ]);
    }

    private function image(int $width, int $height): UploadedFile {
        return UploadedFile::fake()->image('variant.png', $width, $height);
    }

    private function loginUser(): void {
        $this->server->loginById($this->models->newUserReturnId());
    }

    private function loginAdmin(): void {
        $this->server->loginById($this->models->newUserReturnId(permissionNames:['adm-access', 'adm-payment']));
        $this->laravel->withSession(['admin' => true]);
    }

    private function createCampaign(): int {
        /** @var EloquentCampaignsStore $store */
        $store = $this->laravel->app->make(EloquentCampaignsStore::class);
        return $store->createCampaign(new CampaignPayload('campaign', '', null, null, null, null, false, null));
    }
}
