<?php
namespace Tests\Integration\Modules\JobBoard\User\Http;

use Coyote\Modules\JobBoard\User\Http\JobOffersController;
use Coyote\Plan;
use Modules\JobBoard\JobBoardStore;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Legacy\Integration\BaseFixture\Server;

#[CoversClass(JobOffersController::class)]
class JobOffersControllerTest extends TestCase {
    use Server\Laravel\Transactional;

    #[Before(-10)]
    public function givenFreePlan(): void {
        Plan::query()->firstOrCreate(['name' => 'Free']);
    }

    #[Test]
    public function clickJobOffer_returnsSuccess(): void {
        $jobOfferId = $this->store()->createJobOffer('php-developer');
        $this->laravel
            ->post("/job-board/job-offers/$jobOfferId/click")
            ->assertNoContent();
    }

    #[Test]
    public function clickJobOffer_recordsJobOfferClick(): void {
        $jobOfferId = $this->store()->createJobOffer('php-developer');
        $this->laravel->post("/job-board/job-offers/$jobOfferId/click");
        $this->assertSame(1, $this->store()->jobOfferClicks($jobOfferId));
    }

    private function store(): JobBoardStore {
        return $this->laravel->app->make(JobBoardStore::class);
    }
}
