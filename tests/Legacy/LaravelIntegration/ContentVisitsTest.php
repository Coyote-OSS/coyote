<?php
namespace Tests\Legacy\LaravelIntegration;

use Carbon\Carbon;
use Coyote\Microblog;
use Coyote\Page;
use Illuminate\Support\Facades\DB;

class ContentVisitsTest extends TestCase {
    public function testCounterSavesVisitOfUserWithContentType(): void {
        Page::forceCreate(['title' => 'title', 'path' => '/Mikroblogi/View/987654', 'content_type' => Microblog::class]);
        $this->hit('Mikroblogi/View/987654', userId: 1, guestId: null);
        $this->artisan('coyote:counter');
        $this->assertDatabaseHas('content_visits', [
            'user_id'      => 1,
            'guest_id'     => null,
            'content_type' => Microblog::class,
        ]);
    }

    public function testCounterSavesVisitOfGuest(): void {
        $guestId = '0e3c1f4a-6b0a-4d2c-9c1e-7f3b2a1d5e60';
        $this->hit('Not/Existing/Page', userId: null, guestId: $guestId);
        $this->artisan('coyote:counter');
        $this->assertDatabaseHas('content_visits', [
            'user_id'      => null,
            'guest_id'     => $guestId,
            'content_type' => null,
        ]);
    }

    public function testCounterIgnoresAnonymousVisit(): void {
        $before = DB::table('content_visits')->count();
        $this->hit('Anonymous/Page', userId: null, guestId: null);
        $this->artisan('coyote:counter');
        $this->assertSame($before, DB::table('content_visits')->count());
    }

    public function testRollupAggregatesPastMonths(): void {
        DB::table('content_visits')->delete();
        Carbon::setTestNow('2026-11-12 10:00:00');
        $this->visit(5, 'topic', '2026-10-01 00:00:00');
        $this->visit(5, 'topic', '2026-10-31 23:59:00');
        $this->visit(5, 'microblog', '2026-10-15 12:00:00');
        $this->visit(6, 'topic', '2026-09-20 12:00:00');
        $this->visit(5, 'topic', '2026-11-01 00:00:00');

        $this->artisan('visits:rollup');

        $this->assertSame([
            ['2026-09-01', 6, 'topic', 1],
            ['2026-10-01', 5, 'microblog', 1],
            ['2026-10-01', 5, 'topic', 2],
        ], $this->monthly());
        $this->assertSame(1, DB::table('content_visits')->count());
    }

    public function testRollupIsIdempotent(): void {
        DB::table('content_visits')->delete();
        Carbon::setTestNow('2026-11-12 10:00:00');
        $this->visit(5, 'topic', '2026-10-01 00:00:00');
        $this->artisan('visits:rollup');
        $this->artisan('visits:rollup');
        $this->assertSame([['2026-10-01', 5, 'topic', 1]], $this->monthly());
    }

    private function hit(string $path, ?int $userId, ?string $guestId): void {
        app('redis')->sadd('hits', serialize([
            'path'      => $path,
            'timestamp' => (int)(round(time() / 60) * 60),
            'user_id'   => $userId,
            'guest_id'  => $guestId,
        ]));
    }

    private function visit(int $userId, string $contentType, string $createdAt): void {
        DB::table('content_visits')->insert([
            'user_id'      => $userId,
            'content_type' => $contentType,
            'created_at'   => $createdAt,
        ]);
    }

    private function monthly(): array {
        return DB::table('content_visits_monthly')
            ->whereIn('user_id', [5, 6])
            ->orderBy('month')
            ->orderBy('content_type')
            ->get()
            ->map(fn(object $row): array => [$row->month, $row->user_id, $row->content_type, $row->visits])
            ->toArray();
    }
}
