<?php
namespace Tests\Legacy\LaravelIntegration;

use Coyote\Http\Controllers\Adm\CohortController;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Legacy\Integration\BaseFixture\Forum\ModelsDriver;
use Tests\Legacy\Integration\BaseFixture\Server;

#[CoversClass(CohortController::class)]
class CohortControllerTest extends TestCase {
    use Server\Laravel\Transactional;
    use Server\Http;

    #[Before]
    public function loginAdmin(): void {
        $models = new ModelsDriver();
        $this->server->loginById($models->newUserReturnId(permissionNames:['adm-access', 'adm-payment']));
        $this->laravel->withSession(['admin' => true]);
    }

    #[Before]
    public function clearVisits(): void {
        DB::table('content_visits')->delete();
        DB::table('content_visits_monthly')->delete();
    }

    #[Test]
    public function viewCohort_combinesMonthlyRollupWithRawVisits(): void {
        $this->monthlyVisit('2026-09-01', 1);
        $this->monthlyVisit('2026-10-01', 3);
        $this->monthlyVisit('2026-10-01', 4);
        $this->rawVisit('2026-11-05 10:00:00', 3);
        $this->rawVisit('2026-11-06 10:00:00', 5);

        $response = $this->laravel->get('/Adm/Cohort/Download?by=view');

        $response->assertSuccessful();
        $response->assertSee('2026-10-01,2,100%,50%,', escape:false);
        $response->assertSee('2026-11-01,1,100%,', escape:false);
    }

    #[Test]
    public function viewCohort_skipsFirstTrackedMonth(): void {
        $this->monthlyVisit('2026-09-01', 1);
        $this->monthlyVisit('2026-10-01', 2);

        $response = $this->laravel->get('/Adm/Cohort/Download?by=view');

        $response->assertDontSee('2026-09-01');
        $response->assertSee('2026-10-01,1,100%', escape:false);
    }

    #[Test]
    public function viewCohort_ignoresGuests(): void {
        DB::table('content_visits_monthly')->insert([
            'month'    => '2026-09-01',
            'user_id'  => null,
            'guest_id' => '0e3c1f4a-6b0a-4d2c-9c1e-7f3b2a1d5e60',
            'visits'   => 1,
        ]);
        $this->monthlyVisit('2026-10-01', 2);
        $this->monthlyVisit('2026-11-01', 3);

        $response = $this->laravel->get('/Adm/Cohort/Download?by=view');

        $response->assertDontSee('2026-10-01');
        $response->assertSee('2026-11-01,1,100%', escape:false);
    }

    private function monthlyVisit(string $month, int $userId): void {
        DB::table('content_visits_monthly')->insert([
            'month'   => $month,
            'user_id' => $userId,
            'visits'  => 1,
        ]);
    }

    private function rawVisit(string $createdAt, int $userId): void {
        DB::table('content_visits')->insert([
            'user_id'    => $userId,
            'created_at' => $createdAt,
        ]);
    }
}
