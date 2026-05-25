<?php
namespace Modules\Campaigns\Adm\Http;

use Coyote\Modules\Campaigns\Adm;
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
    public function initializeModels(): void {
        $this->models = new ModelsDriver();
    }

    #[Test]
    public function creatingCampaign_failsWithoutAuthorization(): void {
        $this->loginUser();
        $this->laravel->post('/Adm/Campaigns/Save')->assertForbidden();
    }

    #[Test]
    public function creatingCampaign_redirectsToHome(): void {
        $this->loginAdmin();
        $this->laravel
            ->post('/Adm/Campaigns/Save', $this->exampleCampaign())
            ->assertRedirect('/Adm/Campaigns');
    }

    #[Test]
    public function creatingCampaign_savesCampaignInDatabase(): void {
        $this->loginAdmin();
        $this->laravel->post('/Adm/Campaigns/Save', $this->exampleCampaign());
        $this->laravel->assertSeeInDatabase('module_campaigns', $this->exampleCampaign());
    }

    #[Test]
    public function failToCreateDuplicateCampaign(): void {
        $this->loginAdmin();
        $this->laravel->post('/Adm/Campaigns/Save', $this->exampleCampaign('duplicate'));
        $this->laravel
            ->post('/Adm/Campaigns/Save', $this->exampleCampaign('duplicate'))
            ->assertSessionHasErrors([
                'campaign_key' => 'Już istnieje kampania z tym kluczem.',
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

    private function exampleCampaign(?string $campaignKey = null): array {
        return [
            'campaign_key' => $campaignKey ?? 'created-campaign',
            'redirect_url' => 'http://test',
            'sidebar'      => 'image.png',
            'horizontal'   => 'image.png',
            'active_since' => '2000-01-01T00:00:00',
            'active_until' => '2000-01-01T00:00:00',
        ];
    }
}
