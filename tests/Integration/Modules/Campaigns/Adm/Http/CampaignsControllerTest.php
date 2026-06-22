<?php
namespace Tests\Integration\Modules\Campaigns\Adm\Http;

use Coyote\Modules\Campaigns\Adm;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Legacy\IntegrationNew\BaseFixture\Forum\ModelsDriver;
use Tests\Legacy\IntegrationNew\BaseFixture\Server;

#[CoversClass(Adm\Http\CampaignsController::class)]
class CampaignsControllerTest extends TestCase {
    use Server\Laravel\Transactional;
    use Server\RelativeUri;

    private ModelsDriver $models;

    #[Before]
    public function givenAccessToCampaigns(): void {
        $this->models = new ModelsDriver();
        $this->loginAdmin();
    }

    #[Test]
    public function creatingCampaign_failsWithoutAuthorization(): void {
        // given I don't have access to campaigns
        $this->loginUser();
        // when I attempt to create a new campaign
        $response = $this->httpCreate([]);
        // then the request is rejected
        $response->assertForbidden();
    }

    #[Test]
    public function creatingCampaign_redirectsToTheCampaign(): void {
        // when I create a new campaign
        $response = $this->httpCreate($this->exampleCampaign());
        // then I am redirected to the campaign
        $campaignId = $this->campaignId($response);
        $response->assertRedirect("/Adm/Campaigns/Show/$campaignId");
    }

    #[Test]
    public function creatingCampaign_savesCampaignInDatabase(): void {
        // when I create a new campaign
        $this->httpCreate($this->exampleCampaign());
        // then the campaign is persisted
        $this->laravel->assertSeeInDatabase('module_campaigns', $this->exampleCampaign());
    }

    #[Test]
    public function creatingCampaign_allowsOptionalActiveRange(): void {
        // when I create a new camapgin without active range
        $this->httpCreate($this->exampleCampaign(campaignKey:'optional-range', includeActiveRange:false));
        // then the campaign is persisted without active range
        $this->laravel->assertSeeInDatabase('module_campaigns', [
            'name'         => 'optional-range',
            'active_since' => null,
            'active_until' => null,
        ]);
    }

    #[Test]
    public function updateCampaign(): void {
        // given a campaign already exists
        $campaignId = $this->httpCreateReturnId($this->exampleCampaign('updated', redirectUrl:'http://old'));
        // when I update the campaign
        $this->httpUpdate($campaignId, $this->exampleCampaign('updated', redirectUrl:'http://new'));
        // then the campaign is updated
        $this->laravel->assertSeeInDatabase('module_campaigns', [
            'name'         => 'updated',
            'redirect_url' => 'http://new',
        ]);
    }

    #[Test]
    public function routeAliasCreate(): void {
        $this->assertRelativeUri('/Adm/Campaigns/Save', route('adm.campaigns.save'));
    }

    #[Test]
    public function routeAliasHome(): void {
        $this->assertRelativeUri('/Adm/Campaigns', route('adm.campaigns'));
    }

    private function loginUser(): void {
        $this->server->loginById($this->models->newUserReturnId());
    }

    private function loginAdmin(): void {
        $this->server->loginById($this->models->newUserReturnId(permissionName:'adm-access'));
        $this->laravel->withSession(['admin' => true]);
    }

    private function assertRelativeUri(string $expected, string $actual): void {
        $this->assertSame('http://nginx' . $expected, $actual);
    }

    private function exampleCampaign(
        ?string $campaignKey = null,
        ?bool   $includeActiveRange = true,
        ?string $redirectUrl = null,
    ): array {
        return [
            'name'         => $campaignKey,
            'redirect_url' => $redirectUrl ?? 'http://test',
            'sidebar'      => 'not-to-be-used-deprecated',
            'horizontal'   => 'not-to-be-used-deprecated',
            ...$includeActiveRange ? $this->exampleActiveRange() : [],
        ];
    }

    private function exampleActiveRange(): array {
        return [
            'active_since' => '2000-01-01T00:00:00',
            'active_until' => '2000-01-01T00:00:00',
        ];
    }

    private function httpCreateReturnId(array $exampleCampaign): int {
        $response = $this->httpCreate($exampleCampaign);
        $response->assertRedirect();
        return $this->campaignId($response);
    }

    private function httpCreate(array $exampleCampaign): TestResponse {
        return $this->laravel->post('/Adm/Campaigns/Save', $exampleCampaign);
    }

    private function httpUpdate(int $campaignId, array $campaign): void {
        $this->laravel->post("/Adm/Campaigns/Save/$campaignId", $campaign);
    }

    private function campaignId(TestResponse $response): string {
        return $response->headers->get('X-Campaign-Id');
    }
}
