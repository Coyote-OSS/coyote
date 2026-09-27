<?php
namespace Features\Dsl\Driver\Channel\AcceptanceChannel;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Laravel\Dusk;

class BrowserConnection {
    private ?Dusk\Browser $browser = null;

    public function __construct(
        private readonly string $baseUrl,
        private readonly string $userAgent,
    ) {}

    public function initialize(): void {
        Dusk\Browser::$baseUrl = $this->baseUrl;
        $this->browser = new Dusk\Browser($this->remoteWebDriver());
        $this->quitSessionBeforeInteruption();
    }

    public function finalize(): void {
        $this->browser?->quit();
        $this->browser = null;
    }

    public function browser(): Dusk\Browser {
        return $this->browser;
    }

    private function remoteWebDriver(): RemoteWebDriver {
        $chromeOptions = new ChromeOptions();
        $chromeOptions->addArguments([
            '--disable-gpu',
            '--headless',
            '--no-sandbox',
            '--ignore-ssl-errors',
            '--whitelisted-ips=""',
            "--user-agent=$this->userAgent",
        ]);
        $capabilities = DesiredCapabilities::chrome();
        $capabilities->setCapability(ChromeOptions::CAPABILITY, $chromeOptions);
        return RemoteWebDriver::create('http://selenium:4444/wd/hub', $capabilities);
    }

    private function quitSessionBeforeInteruption(): void {
        \register_shutdown_function($this->finalize(...));
    }
}
