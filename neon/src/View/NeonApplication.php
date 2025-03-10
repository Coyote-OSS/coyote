<?php
namespace Neon\View;

class NeonApplication
{
    private ViteManifest $manifest;
    /** @var JobOffer[] */
    private array $jobOffers = [];

    public function __construct()
    {
        $this->manifest = new ViteManifest(__DIR__ . '/../../web/');
    }

    public function addOffer(JobOffer $jobOffer): void
    {
        $this->jobOffers[] = $jobOffer;
    }

    public function htmlMarkupHead(): string
    {
        $styleUrl = $this->assetUrl($this->manifest->styleUrl());
        $faLightUrl = $this->assetUrl($this->manifest->fontAwesomeLightUrl());
        $faSolidUrl = $this->assetUrl($this->manifest->fontAwesomeSolidUrl());
        return <<<head
            <link href='https://fonts.googleapis.com/css?family=Inter' rel='stylesheet'>
            <link rel="stylesheet" href="$styleUrl" title="includeShadowRoot">
            <link rel="preload" href="$faLightUrl" as="font" type="font/woff2" crossorigin>
            <link rel="preload" href="$faSolidUrl" as="font" type="font/woff2" crossorigin>
        head;
    }

    public function htmlMarkupBody(): string
    {
        $scriptUrl = $this->assetUrl($this->manifest->scriptUrl());
        $backendInput = json_encode([
            'jobOffers' => $this->jobOffers,
        ]);
        return <<<body
            <script>window['backendInput'] = $backendInput;</script>
            <div id="vueApplication" style="all:initial;"></div>
            <script src="$scriptUrl"></script>
        body;
    }

    private function assetUrl(string $relativeUrl): string
    {
        return "/neon/$relativeUrl";
    }

    public function assetPath(string $relativePath): string
    {
        return __DIR__ . '/../../web/public/' . \lTrim($relativePath, '/');
    }
}
