<?php
namespace Neon\View;

class JobBoard
{
    private ViteManifest $manifest;
    private array $jobOffers = [];

    public function __construct()
    {
        $this->manifest = new ViteManifest('../neon/web/');
    }

    public function addOffer(string $title): void
    {
        $this->jobOffers[] = $title;
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
            <p>4programmers » Praca</p>
            <p>Oferty Pracy w IT - Odwiedza nas ponad 150 tys. programistów miesięcznie</p>
            <script>var inputData = $inputDataJson;</script>
            <h2>welcome</h2>
            <span class="text-muted">muted</span>
            <div id="jobBoard" class="job-offer__title">
            </div>
            <script src="$scriptUrl"></script>
        body;
    }

    private function assetUrl(string $relativeUrl): string
    {
        return "/neon/$relativeUrl";
    }
}
