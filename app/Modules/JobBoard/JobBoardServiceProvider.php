<?php
namespace Coyote\Modules\JobBoard;

use Coyote\Http\Middleware\AcceptanceTestOnly;
use Coyote\Modules\JobBoard\Eloquent\EloquentJobBoardStore;
use Coyote\Modules\JobBoard\User\Http\JobOffersController;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Modules\JobBoard\JobBoardStore;

class JobBoardServiceProvider extends ServiceProvider {
    public function boot(): void {
        $this->app->bind(
            JobBoardStore::class,
            EloquentJobBoardStore::class);
        $router = $this->app->make(Router::class);
        $this->registerRoutes($router);
        $this->registerRoutesAcceptanceTest($router);
    }

    private function registerRoutes(Router $router): void {
        $router
            ->post('/job-board/job-offers/{jobOfferId}/click', [JobOffersController::class, 'click'])
            ->name('jobBoard.jobOffer.click');
    }

    private function registerRoutesAcceptanceTest(Router $router): void {
        $router->group(
            ['middleware' => ['web', AcceptanceTestOnly::class]],
            $this->registerRoutesHarness(...));
    }

    private function registerRoutesHarness(Router $router): void {
        $router
            ->post('/harness/job-board/reset', function (EloquentJobBoardStore $store) {
                $store->removeJobOffers();
                return response()->noContent();
            });
        $router
            ->post('/harness/job-board/job-offers', function (EloquentJobBoardStore $store) {
                $jobOfferId = $store->createJobOffer(request()->input('title'));
                return response()->json(['id' => $jobOfferId], 201);
            });
        $router
            ->get('/harness/job-board/job-offers/{jobOfferId}/clicks', function (EloquentJobBoardStore $store, int $jobOfferId) {
                return response()->json(['clicks' => $store->jobOfferClicks($jobOfferId)]);
            });
    }
}
