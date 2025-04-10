<?php
namespace Neon2;

readonly class JobBoardView {
    private Web\ViteManifest $vite;

    public function __construct(private \Neon2\JobBoard\JobBoardGate $jobBoard) {
        $this->vite = new \Neon2\Web\ViteManifest(__DIR__ . '/../../web/');
    }

    public function jobBoardView(bool $isTestMode): string {
        $backendInput = $this->encodeBackendInput([
            'jobOffers' => $this->jobBoard->listJobOffers(),
            'testMode'  => $isTestMode,
        ]);
        $entryUrl = "/neon2/static/{$this->vite->scriptUrl()}";
        if ($isTestMode) {
            $paymentProviderScript = null;
        } else {
            $paymentProviderScript = '<script src="https://js.stripe.com/v3/"></script>';
        }
        return <<<html
            <html>
            <head>
              <meta name="viewport" content="width=device-width,initial-scale=1">
              $paymentProviderScript
            </head>
            <body>
              <div id="neonApplication"></div>
              <script type="module" src="$entryUrl"></script>
              <script>var backendInput = $backendInput;</script>
            </body>
            <html>
            html;
    }

    private function encodeBackendInput(array $arr): string {
        return \json_encode($arr, \JSON_THROW_ON_ERROR);
    }
}
