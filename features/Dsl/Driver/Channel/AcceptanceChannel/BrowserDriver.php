<?php
namespace Features\Dsl\Driver\Channel\AcceptanceChannel;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Laravel\Dusk;

class BrowserDriver {
    private ?Dusk\Browser $browser;
    private bool $setUp = false;

    public function __construct(string $baseUrl, string $userAgent) {
        Dusk\Browser::$baseUrl = $baseUrl;
        $this->browser = new Dusk\Browser($this->remoteWebDriver($userAgent));
    }

    /**
     * @param callable $setUp called until it succeeds once for this browser
     */
    public function setUpOnce(callable $setUp): void {
        if (!$this->setUp) {
            $setUp();
            $this->setUp = true;
        }
    }

    public function reset(): void {
        // Leave the page of the previous scenario, so its scripts don't run anymore.
        $this->browser->driver->get('about:blank');
        $this->browser->resize(1366, 1200);
    }

    public function browser(): Dusk\Browser {
        return $this->browser;
    }

    public function submit(string $button): void {
        $this->browser->waitForReload(fn(Dusk\Browser $browser) => $browser->press($button));
    }

    public function screenshot(string $path): void {
        $directory = \dirname($path);
        if (!\is_dir($directory)) {
            \mkdir($directory, 0777, true);
        }
        $this->browser->driver->takeScreenshot($path);
    }

    private function remoteWebDriver(string $userAgent): RemoteWebDriver {
        $chromeOptions = new ChromeOptions();
        $chromeOptions->addArguments([
            '--disable-gpu',
            '--headless',
            '--no-sandbox',
            '--ignore-ssl-errors',
            '--whitelisted-ips=""',
            '--window-size=1366,1200',
            "--user-agent=$userAgent",
            // Pages wait for external scripts and fonts, which only slow the tests down.
            '--host-resolver-rules=MAP * ~NOTFOUND, EXCLUDE nginx, EXCLUDE websocket, EXCLUDE localhost',
        ]);
        $capabilities = DesiredCapabilities::chrome();
        $capabilities->setCapability(ChromeOptions::CAPABILITY, $chromeOptions);
        return RemoteWebDriver::create('http://selenium:4444/wd/hub', $capabilities);
    }

    public function close(): void {
        try {
            $this->browser?->quit();
        } catch (\Throwable) {
            // The browser is already gone.
        }
        $this->browser = null;
    }
}
