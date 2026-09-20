<?php
namespace Features\Dsl\Driver\Channel\AcceptanceChannel;

use Features\Dsl\Driver\Driver;
use Laravel\Dusk\Browser;
use Libs\Arrays\arrays;

class AcceptanceDriver implements Driver {
    private readonly BrowserDriver $driver;
    private readonly HarnessClient $harness;
    private readonly VariantImageFixture $variantImages;
    private readonly CampaignIdMapping $campaignIds;
    private readonly VariantAliasMapping $variantAliases;
    private int $rotationSeed = 0;

    public function __construct() {
        $this->driver = new BrowserDriver($this->userAgentNonCrawler());
        $this->harness = new HarnessClient($this->driver->browser);
        $this->variantImages = new VariantImageFixture();
        $this->campaignIds = new CampaignIdMapping();
        $this->variantAliases = new VariantAliasMapping();
        $this->logIntoAdminPanel();
        $this->harness->resetCampaigns();
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
        $this->campaignIds->setCampaignId($campaign, $this->currentCampaignId());
    }

    public function addVariant(string $campaign, string $variantType, string $variantUrl): void {
        $campaignId = $this->campaignIds->getCampaignId($campaign);
        $imagePath = $this->variantImages->create($variantType);
        try {
            $this->driver->browser->visit("/Adm/Campaigns/Show/$campaignId");
            $this->driver->browser->attach('images[]', $imagePath);
            $this->driver->browser->waitForReload(fn(Browser $browser) => $browser->press('Prześlij'));
            $this->variantAliases->setVariantAlias($this->lastUploadedVariantImageUrl(), $variantUrl);
        } finally {
            \unlink($imagePath);
        }
    }

    public function resolveVariantsForUser(string $deviceType): void {
        [$width, $height] = $this->viewportSize($deviceType);
        $this->driver->browser->resize($width, $height);
        $this->harness->pinRotationSeed($this->rotationSeed++);
        $this->driver->browser->driver->manage()->deleteAllCookies();
        $this->driver->browser->visit('/Forum/Algorytmy/8-algorithms_every_developer_should_know');
        $this->driver->browser->waitUntilMissing('#js-skeleton');
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
            'feed'   => $this->aliasedImageUrls('.campaign-banner-feed img'),
            default  => throw new \Exception("Invalid slot type: $slotType"),
        };
    }

    /**
     * @return string[]
     */
    private function aliasedImageUrls(string $selector): array {
        return $this->imageUrls($selector) |> arrays::map($this->variantAliases->getVariantAlias(...));
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
