<?php
namespace Tests\Legacy\LaravelDusk;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Laravel\Dusk\Browser;
use Laravel\Dusk\Chrome\SupportsChrome;
use Laravel\Dusk\Concerns\ProvidesBrowser;
use RuntimeException;
use Tests\Legacy\LaravelIntegration\CreatesApplication;

abstract class DuskTestCase extends \Illuminate\Foundation\Testing\TestCase
{
    use ProvidesBrowser, SupportsChrome;
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();
        Browser::$baseUrl = $this->baseUrl();
        Browser::$storeScreenshotsAt = base_path('tests/Legacy/LaravelDusk/screenshots');
        Browser::$storeConsoleLogAt = base_path('tests/Legacy/LaravelDusk/console');
        Browser::$storeSourceAt = base_path('tests/Legacy/LaravelDusk/source');
        Browser::$userResolver = function () {
            throw new RuntimeException('User resolver has not been set.');
        };
    }

    private function baseUrl(): string
    {
        return \rTrim(config('app.url'), '/');
    }

    protected function driver(): RemoteWebDriver
    {
        $chromeOptions = new ChromeOptions;
        $chromeOptions->addArguments([
            '--disable-gpu',
            '--headless',
            '--no-sandbox',
            '--ignore-ssl-errors',
            '--whitelisted-ips=""',
        ]);
        $capabilities = DesiredCapabilities::chrome();
        $capabilities->setCapability(ChromeOptions::CAPABILITY, $chromeOptions);
        return RemoteWebDriver::create('http://selenium:4444/wd/hub', $capabilities);
    }
}
