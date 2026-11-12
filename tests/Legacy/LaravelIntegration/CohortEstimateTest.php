<?php
namespace Tests\Legacy\LaravelIntegration;

use Coyote\Http\Controllers\Adm\CohortController;
use Coyote\Services\Adm\Cohort\HistoricPageViewsActivity;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Legacy\Integration\BaseFixture\Forum\ModelsDriver;
use Tests\Legacy\Integration\BaseFixture\Server;

#[CoversClass(CohortController::class)]
#[CoversClass(HistoricPageViewsActivity::class)]
class CohortEstimateTest extends TestCase {
    use Server\Laravel\Transactional;
    use Server\Http;

    #[Before]
    public function loginAdmin(): void {
        $models = new ModelsDriver();
        // registered long ago, so outside of the listed cohorts
        $this->server->loginById($models->newUserReturnId(permissionNames:['adm-access', 'adm-payment']));
        $this->laravel->withSession(['admin' => true]);
    }

    #[Test]
    public function lowerBound_countsMonthsWithTraces(): void {
        $userId = $this->newUser(registeredAt:$this->monthsAgo(3), visitedAt:$this->now());
        $this->stream($userId, $this->monthsAgo(1));

        $response = $this->laravel->get('/Adm/Cohort/Download?by=view-method-server');

        $response->assertSuccessful();
        $response->assertSee($this->cohort(3) . ',1,100%,0%,100%,100%,,', escape:false);
    }

    #[Test]
    public function upperBound_countsEveryMonthUntilLastVisit(): void {
        $userId = $this->newUser(registeredAt:$this->monthsAgo(3), visitedAt:$this->monthsAgo(1));
        $this->stream($userId, $this->monthsAgo(2));

        $response = $this->laravel->get('/Adm/Cohort/Download?by=view-method-client');

        $response->assertSuccessful();
        $response->assertSee($this->cohort(3) . ',1,100%,100%,100%,0%,,', escape:false);
    }

    #[Test]
    public function lowerBound_ignoresSentCopyOfReadMessage(): void {
        $userId = $this->newUser(registeredAt:$this->monthsAgo(2), visitedAt:null);
        DB::table('pm')->insert([
            'user_id'   => $userId,
            'author_id' => $userId,
            'folder'    => 2, // sent; read by the recipient, not by this user
            'text_id'   => DB::table('pm_text')->insertGetId(['text' => 'Hi']),
            'read_at'   => $this->monthsAgo(1),
        ]);

        $response = $this->laravel->get('/Adm/Cohort/Download?by=view-method-server');

        $response->assertSee($this->cohort(2) . ',1,100%,0%,0%,,', escape:false);
    }

    #[Test]
    public function estimates_skipUsersRegisteredBeforeListedCohorts(): void {
        $this->newUser(registeredAt:$this->monthsAgo(30), visitedAt:$this->now());

        $response = $this->laravel->get('/Adm/Cohort/Download?by=view-method-client');

        $response->assertSuccessful();
        $response->assertDontSee($this->cohort(30));
    }

    private function newUser(string $registeredAt, ?string $visitedAt): int {
        return DB::table('users')->insertGetId([
            'name'       => 'user' . \random_int(0, \PHP_INT_MAX),
            'email'      => \random_int(0, \PHP_INT_MAX) . '@example.com',
            'created_at' => $registeredAt,
            'visited_at' => $visitedAt,
        ]);
    }

    private function stream(int $userId, string $createdAt): void {
        DB::table('streams')->insert([
            'verb'       => 'login',
            'actor'      => \json_encode(['id' => $userId]),
            'created_at' => $createdAt,
        ]);
    }

    private function monthsAgo(int $months): string {
        return new \DateTimeImmutable('first day of this month')->modify("-$months months")->format('Y-m-15 12:00:00');
    }

    private function now(): string {
        return new \DateTimeImmutable('-1 minute')->format('Y-m-d H:i:s');
    }

    private function cohort(int $monthsAgo): string {
        return new \DateTimeImmutable('first day of this month')->modify("-$monthsAgo months")->format('Y-m-01');
    }
}
