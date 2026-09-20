<?php
namespace Features\Dsl\Driver\Channel\AcceptanceChannel;

use Features\Dsl\Driver\Driver;
use Laravel\Dusk\Browser;
use Libs\Arrays\arrays;

class AcceptanceDriver implements Driver {
    private readonly BrowserDriver $driver;
    private readonly VariantImageFixture $variantImages;
    /** @var array<string, int> */
    private array $campaignIds = [];
    /** @var array<string, string> */
    private array $variantAliases = [];
    private int $rotationSeed = 0;

    public function __construct() {
        $this->driver = new BrowserDriver($this->userAgentNonCrawler());
        $this->variantImages = new VariantImageFixture();
        $this->logIntoAdminPanel();
        $this->resetCampaigns();
    }

    public function createCampaign(string $campaign, bool $premium): void {
        $this->driver->browser->visit('/Adm/Campaigns/Save');
        $this->driver->browser->type('name', $campaign);
        $this->driver->browser->type('redirect_url', 'https://example.test');
        $this->driver->browser->type('target_views', '999');
        if ($premium) {
            $this->driver->browser->check('is_premium');
        }
        $this->driver->browser->waitForReload(fn(Browser $browser) => $browser->press('Zapisz'));
        $this->campaignIds[$campaign] = $this->currentCampaignId();
    }

    public function addVariant(string $campaign, string $variantType, string $variantUrl): void {
        $campaignId = $this->campaignIds[$campaign];
        $imagePath = $this->variantImages->create($variantType);
        try {
            $this->driver->browser->visit("/Adm/Campaigns/Show/$campaignId");
            $this->driver->browser->attach('images[]', $imagePath);
            $this->driver->browser->waitForReload(fn(Browser $browser) => $browser->press('Prześlij'));
            $this->variantAliases[$this->lastUploadedVariantImageUrl()] = $variantUrl;
        } finally {
            \unlink($imagePath);
        }
    }

    public function resolveVariantsForUser(string $deviceType): void {
        [$width, $height] = $this->viewportSize($deviceType);
        $this->driver->browser->resize($width, $height);
        $this->pinRotationSeed($this->rotationSeed++);
        $this->driver->browser->driver->manage()->deleteAllCookies();
        $this->driver->browser->visit('/');
    }

    private function viewportSize(string $deviceType): array {
        return match ($deviceType) {
            'desktop' => [1920, 1080],
            'mobile'  => [390, 844],
            default   => throw new \Exception("Invalid device type: $deviceType"),
        };
    }

    public function variantsForSlot(string $slotType): array {
        return match ($slotType) {
            'header' => $this->aliasedImageUrls('.campaign-banner-header img'),
            'square' => $this->aliasedImageUrls('.campaign-banner-square img'),
            'feed'   => $this->feedImageUrls(),
            default  => throw new \Exception("Invalid slot type: $slotType"),
        };
    }

    public function close(): void {
        $this->driver->browser->quit();
    }

    private function logIntoAdminPanel(): void {
        $this->driver->browser->visit('/Login');
        $this->closeGdprIfVisible();
        $this->driver->browser->type('name', 'admin');
        $this->driver->browser->type('password', 'admin');
        $this->driver->browser->waitForReload(fn(Browser $browser) => $browser->press('Zaloguj się'));
        $this->driver->browser->visit('/Adm');
        $this->driver->browser->type('password', 'admin');
        $this->driver->browser->waitForReload(fn(Browser $browser) => $browser->press('Logowanie'));
    }

    private function resetCampaigns(): void {
        [$status] = $this->driver->browser->script(<<<'JS'
            var xhr = new XMLHttpRequest();
            xhr.open('POST', '/harness/campaigns/reset', false);
            xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').content);
            xhr.send();
            return xhr.status;
            JS,
        );
        if ($status !== 204) {
            throw new \Exception("Failed to clear campaigns via the test harness (status $status).");
        }
    }

    private function pinRotationSeed(int $seed): void {
        [$status] = $this->driver->browser->script(<<<JS
            var xhr = new XMLHttpRequest();
            xhr.open('POST', '/harness/campaigns/rotation-seed', false);
            xhr.setRequestHeader('Content-Type', 'application/json');
            xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').content);
            xhr.send(JSON.stringify({seed: $seed}));
            return xhr.status;
            JS,
        );
        if ($status !== 204) {
            throw new \Exception("Failed to pin the rotation seed via the test harness (status $status).");
        }
    }

    private function currentCampaignId(): int {
        \preg_match('#/Campaigns/Show/(\d+)#', $this->driver->browser->driver->getCurrentURL(), $matches);
        return (int)$matches[1];
    }

    /**
     * @return string[]
     */
    private function imageUrls(string $selector): array {
        return $this->driver->browser->elements($selector)
                |> arrays::filter(fn($element) => $element->isDisplayed())
                |> arrays::map(fn($element) => $element->getAttribute('src'));
    }

    /**
     * @return string[]
     */
    private function aliasedImageUrls(string $selector): array {
        return \array_map(
            fn(string $url) => $this->variantAliases[$url] ?? throw new \Exception(),
            $this->imageUrls($selector));
    }

    private function feedImageUrls(): array {
        $this->driver->browser->visit('/Forum/Algorytmy/8-algorithms_every_developer_should_know');
        $this->driver->browser->waitUntilMissing('#js-skeleton');
        return $this->aliasedImageUrls('.campaign-banner-feed img');
    }

    private function closeGdprIfVisible(): void {
        $gdprButton = $this->driver->browser->element('#gdpr-all');
        if ($gdprButton?->isDisplayed()) {
            $this->driver->browser->click('#gdpr-all');
            $this->driver->browser->waitUntilMissing('.gdpr-modal');
        }
    }

    private function lastUploadedVariantImageUrl(): string {
        // The campaign's admin page lists variants in creation order.
        $urls = $this->imageUrls('img[alt="Grafika"]');
        return \end($urls);
    }

    private function userAgentNonCrawler(): string {
        return 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36';
    }
}
