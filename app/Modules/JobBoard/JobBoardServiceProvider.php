<?php
namespace Coyote\Modules\JobBoard;

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
        $this->registerRoutes($this->app->make(Router::class));
        $this->registerRoutesHarness($this->app->make(Router::class));
    }

    private function registerRoutes(Router $router): void {
        $router
            ->post('/job-board/job-offers/{jobOfferId}/click', [JobOffersController::class, 'click'])
            ->name('jobBoard.jobOffer.click');
        $router
            ->post('/job-board/job-offers/{jobOfferId}/exposure', [JobOffersController::class, 'expose'])
            ->name('jobBoard.jobOffer.exposure');
    }

    private function registerRoutesHarness(Router $router): void {
        $router
            ->middleware(['web', 'auth', 'can:adm-access', 'adm:1'])
            ->group($this->registerRoutesWebAdmin(...));
    }

    private function registerRoutesWebAdmin(Router $router): void {
        $router->post('/harness/job-board/reset', function (EloquentJobBoardStore $store) {
            $store->removeJobOffers();
            return response()->noContent();
        });
        $router->post('/harness/job-board/job-offers', function (EloquentJobBoardStore $store) {
            $jobOfferId = $store->createJobOffer(request()->input('title'));
            return response()->json(['id' => $jobOfferId], 201);
        });
        $router->get('/harness/job-board/job-offers/{jobOfferId}', function (EloquentJobBoardStore $store, int $jobOfferId) {
            return response()->json([
                'clicks'    => $store->jobOfferClicks($jobOfferId),
                'exposures' => $store->jobOfferExposures($jobOfferId),
            ]);
        });
    }
}
