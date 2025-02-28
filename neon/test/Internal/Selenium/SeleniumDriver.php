<?php
namespace Neon\Test\Internal\Selenium;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;

readonly class SeleniumDriver
{
    private RemoteWebDriver $driver;

    public function __construct()
    {
        $this->driver = $this->createDriver();
    }

    private function createDriver(): RemoteWebDriver
    {
        return RemoteWebDriver::create('http://selenium:4444/wd/hub', $this->capabilities());
    }

    private function capabilities(): DesiredCapabilities
    {
        $capabilities = DesiredCapabilities::chrome();
        $capabilities->setCapability(ChromeOptions::CAPABILITY, $this->chromeOptions());
        return $capabilities;
    }

    private function chromeOptions(): ChromeOptions
    {
        return new ChromeOptions()->addArguments([
            '--disable-gpu',
            '--headless',
            '--no-sandbox',
            '--ignore-ssl-errors',
            '--whitelisted-ips=""',
        ]);
    }

    public function close(): void
    {
        $this->driver->quit();
    }

    public function navigate(string $absoluteUrl): void
    {
        $this->driver->get($absoluteUrl);
    }

    public function captureScreenshot(string $screenshotFilename): void
    {
        $this->driver->takeScreenshot($screenshotFilename);
    }

    public function clearCookies(): void
    {
        $this->driver->manage()->deleteAllCookies();
    }

    public function findByCss(string $cssSelector): SeleniumElement
    {
        return new SeleniumElement($this->driver->findElement(WebDriverBy::cssSelector($cssSelector)));
    }
}
