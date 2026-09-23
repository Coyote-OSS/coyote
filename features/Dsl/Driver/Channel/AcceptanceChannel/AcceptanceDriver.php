<?php
namespace Features\Dsl\Driver\Channel\AcceptanceChannel;

use Features\Dsl\Driver\Driver;
use Libs\Arrays\arrays;

readonly class AcceptanceDriver implements Driver {
    private BrowserDriver $driver;
    private HarnessClient $harness;
    private VariantImageFixture $variantImages;
    private CampaignIdMapping $campaignIds;
    private VariantAliasMapping $variantAliases;
    private RotationSeed $rotationSeed;
    private ScreenshotSequence $screenshots;

    public function __construct(\DateTimeImmutable $testStartDate) {
        $this->driver = new BrowserDriver('http://nginx', $this->userAgentNonCrawler());
        $this->harness = new HarnessClient($this->driver);
        $this->variantImages = new VariantImageFixture();
        $this->campaignIds = new CampaignIdMapping();
        $this->variantAliases = new VariantAliasMapping();
        $this->rotationSeed = new RotationSeed();
        $this->screenshots = new ScreenshotSequence(
            __DIR__ . '/../../../../../storage/screenshots',
            $testStartDate);
    }

    public function initialize(string $feature, string $scenario): void {
        $this->screenshots->startScenario($feature, $scenario);
        $this->driver->initialize();
        $this->logIntoAdminPanel('admin-lowrep', 'admin-lowrep');
        $this->harness->resetCampaigns();
    }

    public function finalize(): void {
        $this->driver->close();
    }

    public function createCampaign(string $campaign, bool $premium): void {
        $this->driver->browser()->visit('/Adm/Campaigns/Save');
        $this->driver->browser()->type('name', $campaign);
        $this->driver->browser()->type('redirect_url', 'https://example.test');
        $this->driver->browser()->type('target_views', '999');
        if ($premium) {
            $this->driver->browser()->check('is_premium');
        }
        $this->driver->submit('Zapisz');
        $this->campaignIds->setCampaignId($campaign, $this->currentCampaignId());
        $this->screenshot('createCampaign');
    }

    public function addVariant(string $campaign, string $variantType, string $variantUrl): void {
        $campaignId = $this->campaignIds->getCampaignId($campaign);
        $imagePath = $this->variantImages->create($variantType);
        try {
            $this->driver->browser()->visit("/Adm/Campaigns/Show/$campaignId");
            $this->driver->browser()->attach('images[]', $imagePath);
            $this->driver->submit('Prześlij');
            $this->variantAliases->setVariantAlias($this->lastUploadedVariantImageUrl(), $variantUrl);
            $this->screenshot('addVariant');
        } finally {
            $this->variantImages->remove($imagePath);
        }
    }

    public function resolveVariantsForUser(string $deviceType): void {
        $this->driver->browser()->resize(...$this->viewportSize($deviceType));
        $this->harness->pinRotationSeed($this->rotationSeed->current());
        $this->rotationSeed->increment();
        $this->resolveAllSlotsForCurrentDevice();
        $this->screenshot('resolveVariantsForUser');
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

    private function logIntoAdminPanel(string $username, string $password): void {
        $this->driver->browser()->visit('/Login');
        $this->closeGdprIfVisible();
        $this->driver->browser()->type('name', $username);
        $this->driver->browser()->type('password', $password);
        $this->driver->submit('Zaloguj się');
        $this->driver->browser()->visit('/Adm');
        $this->driver->browser()->type('password', $password);
        $this->driver->submit('Logowanie');
    }

    private function currentCampaignId(): int {
        \preg_match('#/Campaigns/Show/(\d+)#', $this->driver->browser()->driver->getCurrentURL(), $matches);
        return (int)$matches[1];
    }

    /**
     * @return string[]
     */
    private function imageUrls(string $selector): array {
        return $this->driver->browser()->elements($selector)
                |> arrays::filter(fn($element) => $element->isDisplayed())
                |> arrays::map(fn($element) => $element->getAttribute('src'));
    }

    private function closeGdprIfVisible(): void {
        $gdprButton = $this->driver->browser()->element('#gdpr-all');
        if ($gdprButton?->isDisplayed()) {
            $this->driver->browser()->click('#gdpr-all');
            $this->driver->browser()->waitUntilMissing('.gdpr-modal');
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

    private function resolveAllSlotsForCurrentDevice(): void {
        $this->driver->browser()->visit('/Forum/Algorytmy/8-algorithms_every_developer_should_know');
        $this->driver->browser()->waitUntilMissing('#js-skeleton');
    }

    public function captureDiagnostics(string $testTitle): void {
        $this->screenshot($testTitle);
    }

    private function screenshot(string $label): void {
        $this->driver->screenshot($this->screenshots->nextPath($label));
    }

    public function createJobOffer(string $jobOffer): void {}

    public function clickJobOffer(string $jobOffer): void {}

    public function jobOfferClicks(string $jobOffer): int {
        return 0;
    }
}
