<?php
namespace Features\Dsl\Driver\Channel\AcceptanceChannel;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Features\Dsl\Driver\Driver;
use Laravel\Dusk\Browser;
use Libs\Arrays\arrays;

class AcceptanceDriver implements Driver {
    private const array VARIANT_DIMENSIONS = [
        'banner'         => [728, 90],
        'banner-xl'      => [728, 200],
        'leaderboard'    => [1140, 90],
        'leaderboard-xl' => [1140, 200],
        'rectangle'      => [300, 250],
        'rectangle-xl'   => [300, 600],
    ];

    private const array VIEWPORT_SIZES = [
        'desktop' => [1920, 1080],
        'mobile'  => [390, 844],
    ];

    private readonly Browser $browser;
    /** @var array<string, int> */
    private array $campaignIds = [];
    /** @var array<string, string> real, uploaded image URL => the alias it was given in the scenario */
    private array $variantAliases = [];
    /**
     * Pinned via the test harness on every render, so campaign rotation is deterministic
     * instead of being seeded from the real clock.
     */
    private int $rotationSeed = 0;

    public function __construct() {
        Browser::$baseUrl = 'http://nginx';
        $this->browser = new Browser($this->remoteWebDriver());
        $this->logIntoAdminPanel();
        $this->resetCampaigns();
    }

    public function createCampaign(string $campaign, bool $premium): void {
        $this->browser->visit('/Adm/Campaigns/Save');
        $this->browser->type('name', $campaign);
        $this->browser->type('redirect_url', 'https://example.test');
        $this->browser->type('target_views', '999');
        if ($premium) {
            $this->browser->check('is_premium');
        }
        $this->browser->waitForReload(fn(Browser $browser) => $browser->press('Zapisz'));
        $this->campaignIds[$campaign] = $this->currentCampaignId();
    }

    public function addVariant(string $campaign, string $variantType, string $variantUrl): void {
        $campaignId = $this->campaignIds[$campaign];
        $imagePath = $this->generateVariantImage($variantType);
        try {
            $this->browser->visit("/Adm/Campaigns/Show/$campaignId");
            $this->browser->attach('images[]', $imagePath);
            $this->browser->waitForReload(fn(Browser $browser) => $browser->press('Prześlij'));
            $this->variantAliases[$this->lastUploadedVariantImageUrl()] = $variantUrl;
        } finally {
            \unlink($imagePath);
        }
    }

    public function resolveVariantsForUser(string $deviceType): void {
        [$width, $height] = self::VIEWPORT_SIZES[$deviceType]
            ?? throw new \Exception("Unknown device type: $deviceType");
        $this->browser->resize($width, $height);
        $this->pinRotationSeed($this->rotationSeed++);
        $this->browser->driver->manage()->deleteAllCookies();
        $this->browser->visit('/');
    }

    public function variantsForSlot(string $slotType): array {
        return match ($slotType) {
            'header' => $this->aliasedImageUrls('.campaign-banner-header img'),
            'square' => $this->aliasedImageUrls('.campaign-banner-square img'),
            'feed'   => $this->feedImageUrls(),
            default  => throw new \Exception('Not implemented for the acceptance channel yet.'),
        };
    }

    public function close(): void {
        $this->browser->quit();
    }

    private function logIntoAdminPanel(): void {
        $this->browser->visit('/Login');
        $this->closeGdprIfVisible();
        $this->browser->type('name', 'admin');
        $this->browser->type('password', 'admin');
        $this->browser->waitForReload(fn(Browser $browser) => $browser->press('Zaloguj się'));
        $this->browser->visit('/Adm');
        $this->browser->type('password', 'admin');
        $this->browser->waitForReload(fn(Browser $browser) => $browser->press('Logowanie'));
    }

    /**
     * Scenario isolation: each scenario should start with a clean slate. The admin
     * dashboard page is already loaded (with a CSRF meta tag) after login, so the
     * browser itself makes the request - a synchronous XHR so we can check the result
     * before moving on.
     */
    private function resetCampaigns(): void {
        [$status] = $this->browser->script(<<<'JS'
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

    /**
     * Pins the campaign rotation seed so a render's variant order is deterministic
     * instead of depending on the real clock.
     */
    private function pinRotationSeed(int $seed): void {
        [$status] = $this->browser->script(<<<JS
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
        \preg_match('#/Campaigns/Show/(\d+)#', $this->browser->driver->getCurrentURL(), $matches);
        return (int)$matches[1];
    }

    private function generateVariantImage(string $variantType): string {
        [$width, $height] = self::VARIANT_DIMENSIONS[$variantType]
            ?? throw new \Exception("Unknown variant type: $variantType");
        $path = \sys_get_temp_dir() . '/variant-' . \bin2hex(\random_bytes(8)) . '.png';
        $image = \imageCreateTrueColor($width, $height);
        \imageFill($image, 0, 0, \imageColorAllocate($image, 255, 0, 0));
        \imagePng($image, $path);
        return $path;
    }

    /**
     * @return string[]
     */
    private function imageUrls(string $selector): array {
        return $this->browser->elements($selector)
                |> arrays::filter(fn($element) => $element->isDisplayed())
                |> arrays::map(fn($element) => $element->getAttribute('src'));
    }

    /**
     * Translates real, uploaded image URLs back to the aliases they were given via
     * addVariant() - scenarios assert against those aliases, not the real storage URLs.
     *
     * @return string[]
     */
    private function aliasedImageUrls(string $selector): array {
        return \array_map(
            fn(string $url) => $this->variantAliases[$url] ?? $url,
            $this->imageUrls($selector));
    }

    /**
     * The 'feed' banners are rendered by Vue among the topic's posts, so wait for the
     * server-rendered skeleton (which mounting always removes first) to be gone before
     * reading the DOM - otherwise we might read it before Vue has rendered anything.
     */
    private function feedImageUrls(): array {
        $this->browser->visit('/Forum/Algorytmy/8-algorithms_every_developer_should_know');
        $this->browser->waitUntilMissing('#js-skeleton');
        return $this->aliasedImageUrls('.campaign-banner-feed img');
    }

    /**
     * The campaign's admin page lists variants in creation order, so after uploading
     * one, its row - and real image URL - is the last one in the table.
     */
    private function lastUploadedVariantImageUrl(): string {
        $urls = $this->imageUrls('img[alt="Grafika"]');
        return \end($urls);
    }

    private function closeGdprIfVisible(): void {
        $gdprButton = $this->browser->element('#gdpr-all');
        if ($gdprButton?->isDisplayed()) {
            $this->browser->click('#gdpr-all');
            $this->browser->waitUntilMissing('.gdpr-modal');
        }
    }

    private function remoteWebDriver(): RemoteWebDriver {
        $chromeOptions = new ChromeOptions();
        $chromeOptions->addArguments([
            '--disable-gpu',
            '--headless',
            '--no-sandbox',
            '--ignore-ssl-errors',
            '--whitelisted-ips=""',
            // Chrome's headless UA contains "HeadlessChrome", which the site's bot detection
            // (used to hide campaign banners from crawlers) flags as a robot.
            '--user-agent=Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36',
        ]);
        $capabilities = DesiredCapabilities::chrome();
        $capabilities->setCapability(ChromeOptions::CAPABILITY, $chromeOptions);
        return RemoteWebDriver::create('http://selenium:4444/wd/hub', $capabilities);
    }
}
