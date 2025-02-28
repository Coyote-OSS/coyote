<?php
namespace Neon\Test\Internal;

use Neon\Test\Internal\Selenium\SeleniumDriver;
use Neon\Test\Internal\Selenium\SeleniumElement;

readonly class WebDriver
{
    private SeleniumDriver $selenium;
    private string $baseUrl;

    public function __construct()
    {
        $this->selenium = new SeleniumDriver();
        $this->baseUrl = 'http://nginx/';
    }

    public function close(): void
    {
        $this->selenium->close();
    }

    public function clearCookies(): void
    {
        $this->selenium->clearCookies();
    }

    public function navigate(string $relativeUrl): void
    {
        $this->selenium->navigate(\rTrim($this->baseUrl, '/') . '/' .
            \lTrim($relativeUrl, '/'));
    }

    public function captureScreenshot(string $path, string $testCaseName): void
    {
        $this->selenium->captureScreenshot("$path$testCaseName.png");
    }

    public function find(string $cssSelector): SeleniumElement
    {
        return $this->selenium->findByCss($cssSelector);
    }

    public function resize(int $width, int $height): void
    {
        $this->selenium->resize($width, $height);
    }
}
