<?php
namespace Features\Dsl\Driver\Channel\AcceptanceChannel;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Laravel\Dusk\Browser;

readonly class BrowserDriver {
    public Browser $browser;

    public function __construct(string $userAgent) {
        Browser::$baseUrl = 'http://nginx';
        $this->browser = new Browser($this->remoteWebDriver($userAgent));
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
}
