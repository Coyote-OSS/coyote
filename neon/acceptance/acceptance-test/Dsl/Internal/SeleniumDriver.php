<?php
namespace Neon\Acceptance\Test\Dsl\Internal;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Exception\PhpWebDriverExceptionInterface;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;

readonly class SeleniumDriver
{
    public RemoteWebDriver $driver;

    public function __construct()
    {
        try {
            $this->driver = RemoteWebDriver::create('http://selenium:4444/', $this->headless());
        } catch (PhpWebDriverExceptionInterface $exception) {
            throw new \RuntimeException('Failed to connect to selenium server.', previous:$exception);
        }
        /**
         * When a script is killed from a debugger, the connection is
         * not properly closed and subsequent connections hang.
         */
        \register_shutdown_function($this->close(...));
    }

    private function headless(): DesiredCapabilities
    {
        return DesiredCapabilities::chrome()->setCapability(ChromeOptions::CAPABILITY,
            (new ChromeOptions)->addArguments(['--disable-gpu', '--headless']));
    }

    public function close(): void
    {
        $this->driver->quit();
    }

    public function navigate(string $url): void
    {
        $this->driver->get($url);
    }

    public function shadowRootChild(string $cssSelector): SeleniumElement
    {
        return new SeleniumElement($this->driver
            ->findElement(WebDriverBy::cssSelector($cssSelector))
            ->getShadowRoot()
            ->findElement(WebDriverBy::cssSelector('*')));
    }
}
