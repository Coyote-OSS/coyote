<?php
namespace Tests\Integration\Modules\Campaigns\Harness;

use Coyote\Modules\Campaigns\CampaignsServiceProvider;
use Coyote\Modules\Campaigns\Eloquent\EloquentCampaignsStore;
use Illuminate\Testing\TestResponse;
use Modules\Campaigns\ForRotatingBanners;
use Modules\Campaigns\Store\CampaignPayload;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Legacy\Integration\BaseFixture\Forum\ModelsDriver;
use Tests\Legacy\Integration\BaseFixture\Server;

#[CoversClass(CampaignsServiceProvider::class)]
class CampaignsHarnessTest extends TestCase {
    use Server\Laravel\Transactional;
    use Server\Http;

    private ModelsDriver $models;

    #[Before]
    public function givenModels(): void {
        $this->models = new ModelsDriver();
    }

    #[Test]
    public function removingAllCampaigns_failsWithoutAuthorization(): void {
        // given I don't have access to campaigns
        $this->loginRegularUser();
        // when I attempt to remove all campaigns
        $response = $this->httpRemoveCampaigns();
        // then the request is rejected
        $response->assertForbidden();
    }

    #[Test]
    public function removingAllCampaigns_deletesEveryCampaignFromDatabase(): void {
        // given a couple of campaigns exist
        $this->createCampaign('first-to-remove');
        $this->createCampaign('second-to-remove');
        // and I am authorized as admin
        $this->loginAdmin();
        // when I remove all campaigns
        $this->httpRemoveCampaigns();
        // then no campaigns remain
        $this->assertSame(0, $this->laravel->databaseTable('module_campaigns')->count());
    }

    #[Test]
    public function overridingTheRotationSeed_failsWithoutAuthorization(): void {
        // given I don't have access to campaigns
        $this->loginRegularUser();
        // when I attempt to override the rotation seed
        $response = $this->httpOverrideRotationSeed(7);
        // then the request is rejected
        $response->assertForbidden();
    }

    #[Test]
    public function overridingTheRotationSeed_pinsTheSeedInsteadOfTheClock(): void {
        // given I am authorized as admin
        $this->loginAdmin();
        // when I override the rotation seed
        $this->httpOverrideRotationSeed(7);
        // then the seed is pinned, rather than falling back to the clock
        $this->assertSame(7, $this->integrationGetRotationSeed());
    }

    private function httpOverrideRotationSeed(int $seed): TestResponse {
        return $this->laravel->post('/harness/campaigns/rotation-seed', ['seed' => $seed]);
    }

    private function createCampaign(string $name): void {
        $this->laravel->app->make(EloquentCampaignsStore::class)
            ->createCampaign(new CampaignPayload($name, '', null, null, null, null, false, null));
    }

    private function loginRegularUser(): void {
        $this->server->loginById($this->models->newUserReturnId());
    }

    private function loginAdmin(): void {
        $this->server->loginById($this->models->newUserReturnId(permissionNames:['adm-access']));
        $this->laravel->withSession(['admin' => true]);
    }

    private function httpRemoveCampaigns(): TestResponse {
        return $this->laravel->post('/harness/campaigns/reset');
    }

    private function integrationGetRotationSeed() {
        $rotating = $this->laravel->app->make(ForRotatingBanners::class);
        $rotationSeed = $rotating->rotationSeed();
        return $rotationSeed;
    }
}
