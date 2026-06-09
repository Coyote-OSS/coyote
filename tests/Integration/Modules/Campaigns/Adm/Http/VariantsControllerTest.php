<?php
namespace Adm\Http;

use Coyote\Modules\Campaigns\Adm;
use Illuminate\Testing\TestResponse;
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
}
