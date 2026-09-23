<?php
namespace Coyote\Modules\JobBoard;

use Coyote\Modules\JobBoard\Eloquent\EloquentJobBoardStore;
use Illuminate\Support\ServiceProvider;
use Modules\JobBoard\JobBoardStore;

class JobBoardServiceProvider extends ServiceProvider {
    public function boot(): void {
        $this->app->bind(
            JobBoardStore::class,
            EloquentJobBoardStore::class);
    }
}
