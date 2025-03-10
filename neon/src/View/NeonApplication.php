<?php
namespace Neon\View;

class NeonApplication
{
    private ViteManifest $manifest;
    private array $jobOffers = [];

    public function __construct(private string $basePath)
    {
        $this->manifest = new ViteManifest(__DIR__ . '/../../web/');
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
        $backendInput = json_encode([
            'jobOffers' => $this->jobOffers,
        ]);
        return <<<body
            <div>
                <p>4programmers » Praca</p>
                <p>Oferty Pracy w IT - Odwiedza nas ponad 150 tys. programistów miesięcznie</p>
                <script>var backendInput = $backendInput;</script>
                <div id="jobOffers">
                </div>
            </div>
            <script src="$scriptUrl"></script>
        body;
    }

    private function assetUrl(string $relativeUrl): string
    {
        return \rTrim($this->basePath, '/') . '/' . \lTrim($relativeUrl, '/');
    }

    public function assetPath(string $relativePath): string
    {
        return __DIR__ . '/../../web/public/' . \lTrim($relativePath, '/');
    }
}
