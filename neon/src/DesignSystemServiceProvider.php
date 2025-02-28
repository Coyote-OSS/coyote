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
                    $request->query->get('sectionName', null),
                );
            });
        });
    }

    private function view(string $htmlMarkup, bool $darkTheme, ?string $sectionName): string
    {
        $manifest = $this->staticFilesManifest();
        return $this->webLayout(
            $htmlMarkup,
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

    private function webLayout(string $htmlMarkup, string $theme, string $jsSource, string $cssSource, array $inputData): string
    {
        $inputDataJson = json_encode($inputData);
        return <<<html
            <html lang="en" data-theme="$theme">
            <head>
                <link rel="stylesheet" href="$cssSource">
                <script>var inputData = $inputDataJson;</script>
                <script src="$jsSource"></script>
            </head>
            <body>$htmlMarkup</body>
            </html>
            html;
    }
}
