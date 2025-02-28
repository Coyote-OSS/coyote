<?php
namespace Neon;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class DesignSystemServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Route::middleware('web')->group(function () {
            Route::get('/DesignSystem', function (Request $request): string {
                return $this->view(
                    $request->query->get('htmlMarkup', 'Hello, world!'),
                    $request->query->get('theme', '') === 'dark',
                );
            });
        });
    }

    private function view(string $htmlMarkup, bool $darkTheme): string
    {
        $manifest = $this->staticFilesManifest();
        $jsSource = $this->url($manifest['main.ts']['file']);
        $cssSource = $this->url($manifest['main.ts']['css'][0]);
        $theme = $darkTheme ? 'dark' : 'light';
        return <<<html
            <html lang="en" data-theme="$theme">
            <head>
                <link rel="stylesheet" href="$cssSource">
                <script src="$jsSource"></script>
            </head>
            <body>$htmlMarkup</body>
            </html>
            html;
    }

    private function url(string $relativeUrl): string
    {
        return "/neon/$relativeUrl";
    }

    private function staticFilesManifest(): array
    {
        return $this->parseJson(\file_get_contents('../neon/web/public/manifest.json'));
    }

    private function parseJson(string $jsonContent): array
    {
        return json_decode($jsonContent, true);
    }
}
