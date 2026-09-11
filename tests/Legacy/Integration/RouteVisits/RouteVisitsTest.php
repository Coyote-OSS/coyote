<?php
namespace Tests\Legacy\Integration\RouteVisits;

use Coyote\Domain\RouteVisits;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Legacy\Integration\BaseFixture\Server\Laravel\Transactional;

class RouteVisitsTest extends TestCase
{
    use Transactional;

    private RouteVisits $route;

    #[Before]
    public function initialize(): void
    {
        $this->route = $this->laravel->app->get(RouteVisits::class);
    }

    #[Test]
    public function test(): void
    {
        $this->route->visit('/Foo', '2024-03-03');
        $this->laravel->assertSeeInDatabase('route_visits', [
            'path'   => '/Foo',
            'date'   => '2024-03-03',
            'visits' => 1,
        ]);
    }

    #[Test]
    public function incrementExisting(): void
    {
        $this->route->visit('/Bar', '2024-03-03');
        $this->route->visit('/Bar', '2024-03-03');
        $this->laravel->assertSeeInDatabase('route_visits', [
            'path'   => '/Bar',
            'date'   => '2024-03-03',
            'visits' => 2,
        ]);
    }

    #[Test]
    public function insertWithSeparateDates(): void
    {
        $this->route->visit('/Bar', '2024-01-01');
        $this->route->visit('/Bar', '2024-12-22');
        $this->laravel->assertSeeInDatabase('route_visits', [
            'path' => '/Bar',
            'date' => '2024-01-01',
        ]);
        $this->laravel->assertSeeInDatabase('route_visits', [
            'path' => '/Bar',
            'date' => '2024-12-22',
        ]);
    }

    #[Test]
    public function dontUpdateOtherDate(): void
    {
        $this->route->visit('/Bar', '2024-01-01');
        $this->route->visit('/Bar', '2024-12-22');
        $this->route->visit('/Bar', '2024-12-22');
        $this->laravel->assertSeeInDatabase('route_visits', [
            'date'   => '2024-01-01',
            'visits' => 1,
        ]);
    }

    #[Test]
    public function incrementMultipleTimes(): void
    {
        $this->route->visit('/Bar', '2024-03-03');
        $this->route->visit('/Bar', '2024-03-03');
        $this->route->visit('/Bar', '2024-03-03');
        $this->laravel->assertSeeInDatabase('route_visits', [
            'visits' => 3,
        ]);
    }
}
