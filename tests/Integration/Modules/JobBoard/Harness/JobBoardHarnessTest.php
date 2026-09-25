<?php
namespace Tests\Integration\Modules\JobBoard\Harness;

use Coyote\Modules\JobBoard\Eloquent\EloquentJobBoardStore;
use Coyote\Modules\JobBoard\JobBoardServiceProvider;
use Coyote\Plan;
use Coyote\Projections\ForumJobOffers\ForumJobOffersPresenter;
use Illuminate\Testing\TestResponse;
use Coyote\Services\AcceptanceTest\AcceptanceTest;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Integration\Fixture\Acceptance\ConstantAcceptanceTest;
use Tests\Legacy\Integration\BaseFixture\Server;
use Web\Projections\ForumJobOffers\ViewModel\ForumJobOfferTile;

#[CoversClass(JobBoardServiceProvider::class)]
class JobBoardHarnessTest extends TestCase {
    use Server\Laravel\Transactional;

    private int $freePlanId;

    #[Before(-10)]
    public function givenFreePlan(): void {
        $this->freePlanId = Plan::query()->firstOrCreate(['name' => 'Free'])->id;
    }

    #[Test]
    public function removingAllJobOffers_isNotAvailableOutsideAcceptanceTests(): void {
        // given the application is not running acceptance tests
        $this->givenProductionMode();
        // when I attempt to remove all job offers
        $response = $this->httpRemoveJobOffers();
        // then the harness is not found
        $response->assertNotFound();
    }

    #[Test]
    public function removingAllJobOffers_deletesEveryJobOfferFromDatabase(): void {
        // given a couple of job offers exist
        $this->createJobOffer('first-to-remove');
        $this->createJobOffer('second-to-remove');
        // when I remove all job offers
        $response = $this->httpRemoveJobOffers();
        // then no job offers remain
        $response->assertNoContent();
        $this->assertSame(0, $this->laravel->databaseTable('jobs')->count());
    }

    #[Test]
    public function creatingJobOffer_isNotAvailableOutsideAcceptanceTests(): void {
        // given the application is not running acceptance tests
        $this->givenProductionMode();
        // when I attempt to create a job offer
        $response = $this->httpCreateJobOffer('php-developer');
        // then the harness is not found
        $response->assertNotFound();
    }

    #[Test]
    public function creatingJobOffer_respondsWithJobOfferId(): void {
        // when I create a job offer
        $response = $this->httpCreateJobOffer('php-developer');
        // then the id of the created job offer is returned
        $response->assertCreated();
        $this->laravel->assertSeeInDatabase('jobs', [
            'id'    => $response->json('id'),
            'title' => 'php-developer',
        ]);
    }

    #[Test]
    public function creatingJobOffer_publishesJobOfferWithFreePlan(): void {
        // when I create a job offer
        $response = $this->httpCreateJobOffer('php-developer');
        // then the job offer is published with the free plan
        $this->laravel->assertSeeInDatabase('jobs', [
            'id'         => $response->json('id'),
            'is_publish' => 1,
            'plan_id'    => $this->freePlanId,
        ]);
    }

    #[Test]
    public function creatingJobOffer_presentsJobOfferInForum(): void {
        // when I create a job offer
        $this->httpCreateJobOffer('php-developer');
        // then the job offer is presented in the forum
        $this->assertContains('php-developer', $this->forumJobOfferTitles());
    }

    #[Test]
    public function readingJobOffer_isNotAvailableOutsideAcceptanceTests(): void {
        // given a job offer
        $jobOfferId = $this->createJobOffer('php-developer');
        // and the application is not running acceptance tests
        $this->givenProductionMode();
        // when I attempt to read the job offer
        $response = $this->httpJobOffer($jobOfferId);
        // then the harness is not found
        $response->assertNotFound();
    }

    #[Test]
    public function readingJobOffer_respondsWithClicksFromDatabase(): void {
        // given a job offer clicked twice
        $jobOfferId = $this->createJobOffer('php-developer');
        $this->store()->clickJobOffer($jobOfferId);
        $this->store()->clickJobOffer($jobOfferId);
        // when I read the job offer
        $response = $this->httpJobOffer($jobOfferId);
        // then the clicks are returned
        $response->assertOk();
        $this->assertSame(2, $response->json('clicks'));
    }

    #[Test]
    public function readingJobOffer_respondsWithExposuresFromDatabase(): void {
        // given a job offer exposed twice
        $jobOfferId = $this->createJobOffer('php-developer');
        $this->store()->exposeJobOffer($jobOfferId);
        $this->store()->exposeJobOffer($jobOfferId);
        // when I read the job offer
        $response = $this->httpJobOffer($jobOfferId);
        // then the exposures are returned
        $response->assertOk();
        $this->assertSame(2, $response->json('exposures'));
    }

    private function createJobOffer(string $jobOfferTitle): int {
        return $this->store()->createJobOffer($jobOfferTitle);
    }

    private function store(): EloquentJobBoardStore {
        return $this->laravel->app->make(EloquentJobBoardStore::class);
    }

    /**
     * @return string[]
     */
    private function forumJobOfferTitles(): array {
        $tiles = $this->laravel->app->make(ForumJobOffersPresenter::class)->forumJobOffers();
        return \array_map(fn(ForumJobOfferTile $tile) => $tile->jobOfferTitle, $tiles);
    }

    private function givenProductionMode(): void {
        $this->laravel->app->instance(AcceptanceTest::class, new ConstantAcceptanceTest(false));
    }

    private function httpRemoveJobOffers(): TestResponse {
        return $this->laravel->post('/harness/job-board/reset');
    }

    private function httpCreateJobOffer(string $jobOfferTitle): TestResponse {
        return $this->laravel->post('/harness/job-board/job-offers', ['title' => $jobOfferTitle]);
    }

    private function httpJobOffer(int $jobOfferId): TestResponse {
        return $this->laravel->get("/harness/job-board/job-offers/$jobOfferId");
    }
}
