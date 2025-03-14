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

    public function htmlMarkupBody(): string
    {
        $jobOffers = json_encode($this->offers);
        $scriptUrl = $this->url($this->vite->scriptUrl());
        return <<<html
            <div id="neon-application">
                <div id="vueApplication"></div>
            </div>
            <script>var jobOffers = {$jobOffers};</script>
            <script src="{$scriptUrl}"></script>        
            html;
    }

    public function htmlMarkupHead(): string
    {
        $styleUrl = $this->url($this->vite->styleUrl());
        $faLightUrl = $this->url($this->vite->fontAwesomeLightUrl());
        $faSolidUrl = $this->url($this->vite->fontAwesomeSolidUrl());
        return <<<head
            <link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Inter:500,700">
            <link rel="stylesheet" type="text/css" href="$styleUrl" title="includeShadowRoot">
            <link rel="preload" href="$faLightUrl" as="font" type="font/woff2" crossorigin>
            <link rel="preload" href="$faSolidUrl" as="font" type="font/woff2" crossorigin>
        head;
    }

    public function viteFile(string $file): string
    {
        return $this->vite->fileContent($file);
    }

    public function addJobOffer(
        string  $jobOfferTitle,
        string  $jobOfferUrl,
        string  $publishDate,
        ?int    $salaryFrom,
        ?int    $salaryTo,
        string  $workMode,
        array   $locations,
        ?string $companyName,
    ): void
    {
        $this->offers[] = [
            'title'       => $jobOfferTitle,
            'url'         => $jobOfferUrl,
            'publishDate' => $publishDate,
            'salaryFrom'  => $salaryFrom,
            'salaryTo'    => $salaryTo,
            'workMode'    => $workMode,
            'locations'   => $locations,
            'companyName' => $companyName,
        ];
    }

    private function url(string $path): string
    {
        return \rTrim($this->basePath, '/') . '/' . $path;
    }
}
