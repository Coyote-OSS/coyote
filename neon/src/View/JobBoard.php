<?php
namespace Neon\View;

class JobBoard
{
    private ViteManifest $manifest;
    /** @var JobOffer[] */
    private array $jobOffers = [];

    public function __construct()
    {
        $this->manifest = new ViteManifest('../neon/web/');
    }

    public function addOffer(JobOffer $jobOffer): void
    {
        $this->jobOffers[] = $jobOffer;
    }

    public function htmlMarkupHead(): string
    {
        $styleUrl = $this->assetUrl($this->manifest->styleUrl());
        $faLightUrl = $this->assetUrl($this->manifest->fontAwesomeLightUrl());
        return <<<head
            <link rel="stylesheet" href="$styleUrl" title="includeShadowRoot">
            <link rel="preload" href="$faLightUrl" as="font" type="font/woff2" crossorigin>
        head;
    }

    public function htmlMarkupBody(): string
    {
        $scriptUrl = $this->assetUrl($this->manifest->scriptUrl());
        $backendInput = $this->json(['jobOffers' => $this->jobOffers]);
        return <<<body
            <script>window['backendInput'] = $backendInput;</script>
            <div id="jobBoard"></div>
            <script src="$scriptUrl"></script>
        body;
    }

    private function json(array $data): string
    {
        return \json_encode($data, \JSON_THROW_ON_ERROR);
    }

    private function assetUrl(string $relativeUrl): string
    {
        return "/neon/$relativeUrl";
    }
}
