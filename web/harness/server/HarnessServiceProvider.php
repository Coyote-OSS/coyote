<?php
namespace Web\Harness;

use Coyote\Http\Middleware\AcceptanceTestOnly;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use function base_path;

class HarnessServiceProvider extends ServiceProvider {
    public function register(): void {
        $this->app->instance(HarnessBuild::class,
            new HarnessBuild(base_path('web/harness/client/dist')));
    }

    public function boot(): void {
        $this->app->make(Router::class)
            ->middleware(AcceptanceTestOnly::class)
            ->group($this->registerRoutes(...));
    }

    private function registerRoutes(Router $router): void {
        $router->get('/harness', fn() => $this->staticFile('index.html'));
        $router->get('/harness/assets/{asset}', fn(string $asset) => $this->staticFile("assets/$asset"));
    }

    private function staticFile(string $path): BinaryFileResponse {
        return response()->file($this->builtFile($path), [
            'Content-Type' => $this->contentType($path),
        ]);
    }

    private function builtFile(string $path): string {
        return $this->app->make(HarnessBuild::class)->file($path) ?? abort(404);
    }

    private function contentType(string $path): string {
        return match (\pathInfo($path, \PATHINFO_EXTENSION)) {
            'html'  => 'text/html',
            'js'    => 'text/javascript',
            'css'   => 'text/css',
            'woff2' => 'font/woff2',
        };
    }
}
