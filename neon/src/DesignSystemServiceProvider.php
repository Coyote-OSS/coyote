<?php
namespace Neon;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class DesignSystemServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (config('app.debug')) {
            $this->bootServiceProvider();
        }
    }

    private function bootServiceProvider(): void
    {
        Route::middleware('web')->group(function () {
            Route::get('/DesignSystem', function (Request $request): string {
                return $this->view(
                    $request->query->get('section'),
                    $request->query->get('theme', '') === 'dark');
            });
            Route::get('/Praca/New', function (Request $request): string {
                return $this->view('jobboard', $request->query->get('theme', '') === 'dark');
            });
        });
    }

    private function view(?string $sectionName, bool $darkTheme): string
    {
        $manifest = $this->staticFilesManifest();
        return $this->webLayout(
            $darkTheme ? 'dark' : 'light',
            $this->url($manifest['main.ts']['file']),
            $this->url($manifest['main.ts']['css'][0]),
            ['sectionName' => $sectionName]);
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

    private function webLayout(string $theme, string $jsSource, string $cssSource, array $inputData): string
    {
        $inputDataJson = json_encode($inputData);
        return <<<html
            <html lang="en" data-theme="$theme">
            <head>
                <link rel="stylesheet" href="$cssSource">
                <script>var inputData = $inputDataJson;</script>
                <script src="$jsSource"></script>
            </head>
            <body></body>
            </html>
            html;
    }
}
