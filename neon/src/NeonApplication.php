<?php
namespace Neon;

class NeonApplication
{
    private ViteManifest $vite;
    private array $offers = [];

    public function __construct(private string $basePath)
    {
        $this->vite = new ViteManifest(__DIR__ . '/../web/');
    }

    public function htmlMarkupBody(Theme $theme): string
    {
        $jobOffers = \json_encode($this->offers);
        $scriptUrl = $this->url($this->vite->scriptUrl());
        $themeAttribute = $this->theme($theme);
        return <<<html
            <div id="neon-application" data-theme="$themeAttribute">
                <div id="vueApplication" class="bg-body"></div>
            </div>
            <script>var jobOffers = {$jobOffers};</script>
            <script src="{$scriptUrl}"></script>        
            html;
    }

    public function htmlMarkupHead(): string
    {
        $styleUrl = $this->url($this->vite->styleUrl());
        $faLightUrl = $this->url($this->vite->fontAwesomeLightUrl());
        $faRegularUrl = $this->url($this->vite->fontAwesomeRegularUrl());
        $faSolidUrl = $this->url($this->vite->fontAwesomeSolidUrl());
        return <<<head
            <link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Inter:400,500,700">
            <link rel="stylesheet" type="text/css" href="$styleUrl" title="includeShadowRoot">
            <link rel="preload" href="$faLightUrl" as="font" type="font/woff2" crossorigin>
            <link rel="preload" href="$faRegularUrl" as="font" type="font/woff2" crossorigin>
            <link rel="preload" href="$faSolidUrl" as="font" type="font/woff2" crossorigin>
        head;
    }

    public function addJobOffer(JobOffer $jobOffer): void
    {
        $this->offers[] = $jobOffer;
    }

    private function url(string $path): string
    {
        return \rTrim($this->basePath, '/') . '/' . $path;
    }

    private function theme(Theme $theme): string
    {
        return match ($theme) {
            Theme::Light => 'theme-light',
            Theme::Dark => 'theme-dark',
        };
    }
}
