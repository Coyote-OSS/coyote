<?php
namespace Neon2;

readonly class JobBoardView {
    private Web\ViteManifest $vite;

    public function __construct(private \Neon2\JobBoard\JobBoardGate $jobBoard) {
        $this->vite = new \Neon2\Web\ViteManifest(__DIR__ . '/../../web/');
    }

    public function jobBoardView(): string {
        $backendInput = \json_encode(['jobOffers' => $this->jobBoard->listJobOffers()], \JSON_THROW_ON_ERROR);
        $entryUrl = "/neon2/static/{$this->vite->scriptUrl()}";
        return <<<html
            <div id="neonApplication"></div>
            <script type="module" src="$entryUrl"></script>
            <script>var backendInput = $backendInput;</script>
            html;
    }
}
