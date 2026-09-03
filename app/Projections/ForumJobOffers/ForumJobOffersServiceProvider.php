<?php
namespace Coyote\Projections\ForumJobOffers;

use Illuminate\Support\ServiceProvider;

class ForumJobOffersServiceProvider extends ServiceProvider {
    public function register(): void {
        $this->app->bind(Shuffler::class, RandomShuffler::class);
    }
}
