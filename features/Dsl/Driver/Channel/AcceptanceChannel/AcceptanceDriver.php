<?php
namespace Features\Dsl\Driver\Channel\AcceptanceChannel;

use Facebook\WebDriver\Exception\TimeoutException;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverElement;
use Features\Dsl\Driver\Driver;
use Libs\Arrays\arrays;

readonly class AcceptanceDriver implements Driver {
    private HarnessClient $harness;
    private VariantImageFixture $variantImages;
    private CampaignIdMapping $campaignIds;
    private JobOfferIdMapping $jobOfferIds;
    private VariantAliasMapping $variantAliases;
    private RotationSeed $rotationSeed;
    private ScreenshotSequence $screenshots;

    public function __construct(
        private BrowserDriver $driver,
        \DateTimeImmutable    $testStartDate,
        string                $harnessBaseUrl,
        private bool          $stepScreenshots,
    ) {
        $this->harness = new HarnessClient($harnessBaseUrl);
        $this->variantImages = new VariantImageFixture();
        $this->campaignIds = new CampaignIdMapping();
        $this->jobOfferIds = new JobOfferIdMapping();
        $this->variantAliases = new VariantAliasMapping();
        $this->rotationSeed = new RotationSeed();
        $this->screenshots = new ScreenshotSequence(
            __DIR__ . '/../../../../../storage/screenshots',
            $testStartDate);
    }

    public function initialize(string $feature, string $scenario): void {
        $this->screenshots->startScenario($feature, $scenario);
        $this->driver->reset();
        $this->driver->setUpOnce(fn() => $this->logIntoAdminPanel('admin-lowrep', 'admin-lowrep'));
        $this->harness->resetCampaigns();
        $this->harness->resetJobOffers();
    }

    public function finalize(): void {}

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

    private function resolveAllSlotsForCurrentDevice(): void {
        $this->visitTopic();
    }

    private function visitTopic(): void {
        $this->driver->browser()->visit('/Forum/Algorytmy/8-algorithms_every_developer_should_know');
        $this->driver->browser()->waitUntilMissing('#js-skeleton');
    }

    public function captureDiagnostics(string $testTitle): void {
        $this->driver->screenshot($this->screenshots->nextPath($testTitle));
    }

    private function screenshot(string $label): void {
        if ($this->stepScreenshots) {
            $this->driver->screenshot($this->screenshots->nextPath($label));
        }
    }

    public function createJobOffer(string $jobOffer): void {
        $this->jobOfferIds->setJobOfferId($jobOffer, $this->harness->createJobOffer($jobOffer));
    }

    public function clickJobOffer(string $jobOffer): void {
        // With fewer than 3 job offers, the tiles are only shown on mobile.
        $this->driver->browser()->resize(...$this->viewportSize('mobile'));
        $this->visitTopic();
        $tile = $this->jobOfferTile($jobOffer);
        $this->screenshot('clickJobOffer');
        $this->driver->browser()->waitForReload(fn() => $tile->click());
    }

    public function jobOfferClicks(string $jobOffer): int {
        return $this->harness->jobOfferClicks($this->jobOfferIds->getJobOfferId($jobOffer));
    }

    private function jobOfferTile(string $jobOffer): WebDriverElement {
        $tile = null;
        try {
            $this->driver->browser()->waitUsing(5, 100, function () use ($jobOffer, &$tile): bool {
                $tile = $this->displayedJobOfferTile($jobOffer);
                return $tile !== null;
            });
        } catch (TimeoutException) {
            throw new \Exception("Job offer tile is not displayed: $jobOffer");
        }
        return $tile;
    }

    private function displayedJobOfferTile(string $jobOffer): ?WebDriverElement {
        // Job offer tiles are rendered between posts, in the shadow DOM of <vue-shadow-root>,
        // which is attached only once the custom element is defined.
        foreach ($this->driver->browser()->elements('vue-shadow-root') as $shadowHost) {
            $tiles = $shadowHost->getShadowRoot()->findElements(WebDriverBy::cssSelector('a'));
            foreach ($tiles as $tile) {
                if ($tile->isDisplayed() && \str_contains($tile->getText(), $jobOffer)) {
                    return $tile;
                }
            }
        }
        return null;
    }

    public function exposeJobOffer(string $jobOffer): void {
        $exposuresBefore = $this->jobOfferExposures($jobOffer);
        // With fewer than 3 job offers, the tiles are only shown on mobile.
        $this->driver->browser()->resize(...$this->viewportSize('mobile'));
        $this->visitTopic();
        $tile = $this->jobOfferTile($jobOffer);
        $this->driver->browser()->driver->executeScript(
            'arguments[0].scrollIntoView({block: "center"});',
            [$tile]);
        $this->screenshot('exposeJobOffer');
        $this->waitUntilExposureIsCounted($jobOffer, $exposuresBefore);
    }

    private function waitUntilExposureIsCounted(string $jobOffer, int $exposuresBefore): void {
        // The tile must stay in view for a while before the exposure is sent,
        // so wait for it to be counted, instead of guessing the time.
        try {
            $this->driver->browser()->waitUsing(5, 250,
                fn(): bool => $this->jobOfferExposures($jobOffer) > $exposuresBefore);
        } catch (TimeoutException) {
            // An exposure that is never counted is reported by the assertion on exposures.
        }
    }

    public function jobOfferExposures(string $jobOffer): int {
        return $this->harness->jobOfferExposures($this->jobOfferIds->getJobOfferId($jobOffer));
    }
}
