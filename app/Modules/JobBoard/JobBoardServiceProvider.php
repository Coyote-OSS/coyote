<?php
namespace Coyote\Modules\JobBoard;

use Coyote\Modules\JobBoard\Eloquent\EloquentJobBoardStore;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Modules\JobBoard\JobBoardStore;

class JobBoardServiceProvider extends ServiceProvider {
    public function boot(): void {
        $this->app->bind(
            JobBoardStore::class,
            EloquentJobBoardStore::class);
        $this->registerRoutes($this->app->make(Router::class));
    }

    private function registerRoutes(Router $router): void {
        $router
            ->post('/harness/job-board/reset', function (EloquentJobBoardStore $store) {
                $store->removeJobOffers();
                return response()->noContent();
            })
            ->middleware(['web', 'auth', 'can:adm-access', 'adm:1']);
        $router
            ->post('/harness/job-board/job-offers', function (EloquentJobBoardStore $store) {
                $jobOfferId = $store->createJobOffer(request()->input('title'));
                return response()->json(['id' => $jobOfferId], 201);
            })
            ->middleware(['web', 'auth', 'can:adm-access', 'adm:1']);
        $router
            ->get('/harness/job-board/job-offers/{jobOfferId}/clicks', function (EloquentJobBoardStore $store, int $jobOfferId) {
                return response()->json(['clicks' => $store->jobOfferClicks($jobOfferId)]);
            })
            ->middleware(['web', 'auth', 'can:adm-access', 'adm:1']);
    }
}
