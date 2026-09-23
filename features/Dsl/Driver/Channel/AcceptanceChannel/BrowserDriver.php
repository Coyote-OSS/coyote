<?php
namespace Features\Dsl\Driver\Channel\AcceptanceChannel;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Laravel\Dusk;

readonly class BrowserDriver {
    private ?Dusk\Browser $browser;

    public function __construct(
        private string $baseUrl,
        private string $userAgent,
    ) {}

    public function initialize(): void {
        Dusk\Browser::$baseUrl = $this->baseUrl;
        $this->browser = new Dusk\Browser($this->remoteWebDriver($this->userAgent));
    }

    public function browser(): Dusk\Browser {
        return $this->browser;
    }

    public function submit(string $button): void {
        $this->browser->waitForReload(fn(Dusk\Browser $browser) => $browser->press($button));
    }

    private function remoteWebDriver(string $userAgent): RemoteWebDriver {
        $chromeOptions = new ChromeOptions();
        $chromeOptions->addArguments([
            '--disable-gpu',
            '--headless',
            '--no-sandbox',
            '--ignore-ssl-errors',
            '--whitelisted-ips=""',
            "--user-agent=$userAgent",
        ]);
        $capabilities = DesiredCapabilities::chrome();
        $capabilities->setCapability(ChromeOptions::CAPABILITY, $chromeOptions);
        return RemoteWebDriver::create('http://selenium:4444/wd/hub', $capabilities);
    }

    public function close(): void {
        $this->browser->quit();
    }
}
