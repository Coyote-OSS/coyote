<?php
namespace Tests\Integration\Modules\JobBoard\Harness;

use Coyote\Modules\JobBoard\Eloquent\EloquentJobBoardStore;
use Coyote\Modules\JobBoard\JobBoardServiceProvider;
use Coyote\Plan;
use Coyote\Projections\ForumJobOffers\ForumJobOffersPresenter;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Legacy\Integration\BaseFixture\Forum\ModelsDriver;
use Tests\Legacy\Integration\BaseFixture\Server;
use Web\Projections\ForumJobOffers\ViewModel\ForumJobOfferTile;

#[CoversClass(JobBoardServiceProvider::class)]
class JobBoardHarnessTest extends TestCase {
    use Server\Laravel\Transactional;
    use Server\Http;

    private ModelsDriver $models;
    private int $freePlanId;

    #[Before]
    public function givenModels(): void {
        $this->models = new ModelsDriver();
    }

    #[Before(-10)]
    public function givenFreePlan(): void {
        $this->freePlanId = Plan::query()->forceCreate(['name' => 'Free'])->id;
    }

    #[Test]
    public function removingAllJobOffers_failsWithoutAuthorization(): void {
        // given I don't have access to the job board harness
        $this->loginRegularUser();
        // when I attempt to remove all job offers
        $response = $this->httpRemoveJobOffers();
        // then the request is rejected
        $response->assertForbidden();
    }

    #[Test]
    public function removingAllJobOffers_deletesEveryJobOfferFromDatabase(): void {
        // given a couple of job offers exist
        $this->createJobOffer('first-to-remove');
        $this->createJobOffer('second-to-remove');
        // and I am authorized as admin
        $this->loginAdmin();
        // when I remove all job offers
        $response = $this->httpRemoveJobOffers();
        // then no job offers remain
        $response->assertNoContent();
        $this->assertSame(0, $this->laravel->databaseTable('jobs')->count());
    }

    #[Test]
    public function creatingJobOffer_failsWithoutAuthorization(): void {
        // given I don't have access to the job board harness
        $this->loginRegularUser();
        // when I attempt to create a job offer
        $response = $this->httpCreateJobOffer('php-developer');
        // then the request is rejected
        $response->assertForbidden();
    }

    #[Test]
    public function creatingJobOffer_respondsWithJobOfferId(): void {
        // given I am authorized as admin
        $this->loginAdmin();
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
        // given I am authorized as admin
        $this->loginAdmin();
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
        // given I am authorized as admin
        $this->loginAdmin();
        // when I create a job offer
        $this->httpCreateJobOffer('php-developer');
        // then the job offer is presented in the forum
        $this->assertContains('php-developer', $this->forumJobOfferTitles());
    }

    #[Test]
    public function readingJobOfferClicks_failsWithoutAuthorization(): void {
        // given a job offer
        $jobOfferId = $this->createJobOffer('php-developer');
        // and I don't have access to the job board harness
        $this->loginRegularUser();
        // when I attempt to read the job offer clicks
        $response = $this->httpJobOfferClicks($jobOfferId);
        // then the request is rejected
        $response->assertForbidden();
    }

    #[Test]
    public function readingJobOfferClicks_respondsWithClicksFromDatabase(): void {
        // given a job offer clicked twice
        $jobOfferId = $this->createJobOffer('php-developer');
        $this->store()->clickJobOffer($jobOfferId);
        $this->store()->clickJobOffer($jobOfferId);
        // and I am authorized as admin
        $this->loginAdmin();
        // when I read the job offer clicks
        $response = $this->httpJobOfferClicks($jobOfferId);
        // then the clicks are returned
        $response->assertOk();
        $this->assertSame(2, $response->json('clicks'));
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

    private function loginRegularUser(): void {
        $this->server->loginById($this->models->newUserReturnId());
    }

    private function loginAdmin(): void {
        $this->server->loginById($this->models->newUserReturnId(permissionNames:['adm-access']));
        $this->laravel->withSession(['admin' => true]);
    }

    private function httpRemoveJobOffers(): TestResponse {
        return $this->laravel->post('/harness/job-board/reset');
    }

    private function httpCreateJobOffer(string $jobOfferTitle): TestResponse {
        return $this->laravel->post('/harness/job-board/job-offers', ['title' => $jobOfferTitle]);
    }

    private function httpJobOfferClicks(int $jobOfferId): TestResponse {
        return $this->laravel->get("/harness/job-board/job-offers/$jobOfferId/clicks");
    }
}
