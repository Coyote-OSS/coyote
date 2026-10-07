<?php
namespace Tests\Legacy\LaravelIntegration;

use Coyote\Http\Controllers\Adm\StatisticsController;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Legacy\Integration\BaseFixture\Forum\ModelsDriver;
use Tests\Legacy\Integration\BaseFixture\Server;

#[CoversClass(StatisticsController::class)]
class StatisticsControllerTest extends TestCase {
    use Server\Laravel\Transactional;
    use Server\Http;

    #[Before]
    public function loginAdmin(): void {
        $models = new ModelsDriver();
        $this->server->loginById($models->newUserReturnId(permissionNames:['adm-access']));
        $this->laravel->withSession(['admin' => true]);
    }

    #[Test]
    public function statisticsPage_showsCharts(): void {
        $response = $this->laravel->get('/Adm/Statistics');
        $response->assertSuccessful();
        $response->assertSee('registration-history-chart-');
    }

    #[Test]
    public function dashboard_doesNotShowCharts(): void {
        $response = $this->laravel->get('/Adm/Dashboard');
        $response->assertSuccessful();
        $response->assertDontSee('registration-history-chart-');
    }

    #[Test]
    public function dashboard_linksToStatistics(): void {
        $response = $this->laravel->get('/Adm/Dashboard');
        $response->assertSee('/Adm/Statistics');
    }
}
