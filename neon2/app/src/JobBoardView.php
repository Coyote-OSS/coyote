<?php
namespace Neon2;

readonly class JobBoardView {
    private Web\ViteManifest $vite;

    public function __construct(
        private \Neon2\Coyote\Integration  $integration,
        private \Neon2\Invoice\CountryGate $countries,
        private ?string                    $stripePublishableKey,
        private ?string                    $googleMapsKey,
        private bool                       $isTestMode,
        private ?int                       $userId,
        private ?string                    $userEmail,
        private string                     $csrfToken,
        private bool                       $darkTheme,
        private string                     $themeMode,
    ) {
        $this->vite = new \Neon2\Web\ViteManifest(__DIR__ . '/../../web/');
    }

    public function htmlMarkupHead(): string {
        $styleUrl = "/neon2/static/{$this->vite->styleUrl()}";
        if ($this->isTestMode) {
            $resources = null;
        } else {
            $resources = '<link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Inter+Tight:400,500,600,700">';
        }
        $faLightUrl = "/neon2/static/{$this->vite->fontAwesomeLightUrl()}";
        return <<<html
            <meta name="viewport" content="width=device-width,initial-scale=1">
            <link rel="stylesheet" type="text/css" href="$styleUrl" title="includeInShadowRoot">
            <link rel="preload" as="font" type="font/woff2" crossorigin href="$faLightUrl">
            $resources
            html;
    }

    public function htmlMarkupBody(): string {
        if ($this->isTestMode) {
            $resource = null;
        } else {
            $resource = <<<htmlResource
                <script async src="https://maps.googleapis.com/maps/api/js?key=$this->googleMapsKey&libraries=places&loading=async&callback=googleMapsApiLoaded"></script>
                htmlResource;
        }
        return <<<html
            <custom-neon-application>
                <div id="creditCardInput"></div>
            </custom-neon-application>
            <script type="module" src="{$this->entryScriptUrl()}"></script>
            <script>var backendInput = {$this->backendInput()};</script>
            <script>
            let wasLoaded = false;
            const queuedCalls = [];
            function googleMapsApiLoaded() {
                wasLoaded = true;
                for (const queuedCall of queuedCalls) queuedCall();
            }
            function onGoogleMapsLoaded(callback) {
                if (wasLoaded) { callback(); return; }
                queuedCalls.push(callback);
            }
            </script>
            $resource
            html;
    }

    private function entryScriptUrl(): string {
        return "/neon2/static/{$this->vite->scriptUrl()}";
    }

    private function backendInput(): string {
        return $this->encodedJson([
            'jobOffers'                => $this->integration->listJobOffers(),
            'testMode'                 => $this->isTestMode,
            'planBundle'               => $this->integration->planBundle($this->userId),
            'userId'                   => $this->userId,
            'jobOfferApplicationEmail' => $this->userEmail,
            'csrfToken'                => $this->csrfToken,
            'stripePublishableKey'     => $this->stripePublishableKey,
            'paymentInvoiceCountries'  => $this->countries->invoiceCountries(),
            'darkTheme'                => $this->darkTheme,
            'themeMode'                => $this->themeMode,
        ]);
    }

    private function encodedJson(array $arr): string {
        return \json_encode($arr, \JSON_THROW_ON_ERROR);
    }
}
