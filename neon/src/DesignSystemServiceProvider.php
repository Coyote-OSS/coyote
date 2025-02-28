<?php
namespace Neon;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class DesignSystemServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Route::middleware('web')->group(function () {
            Route::get('/DesignSystem', function (): string {
                return '<p>Hello, World!</p>';
            });
        });
    }
}
