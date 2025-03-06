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
        return <<<head
            <link rel="stylesheet" href="$styleUrl" title="includeShadowRoot">
        head;
    }

    public function htmlMarkupBody(): string
    {
        $scriptUrl = $this->assetUrl($this->manifest->scriptUrl());
        $inputDataJson = json_encode([
            'jobOffers' => $this->jobOffers,
        ]);
        return <<<body
            <script>window['backendInput'] = $inputDataJson;</script>
            <div id="jobBoard" class="job-offer__title"></div>
            <script src="$scriptUrl"></script>
        body;
    }

    private function assetUrl(string $relativeUrl): string
    {
        return "/neon/$relativeUrl";
    }
}
