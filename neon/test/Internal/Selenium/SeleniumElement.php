<?php
namespace Neon\Test\Internal\Selenium;

use Facebook\WebDriver\Remote\RemoteWebElement;

readonly class SeleniumElement
{
    public function __construct(private RemoteWebElement $element) {}

    public function fill(string $value): void
    {
        $this->element->clear()->sendKeys($value);
    }

    public function click(): void
    {
        $this->element->click();
    }

    public function text(): string
    {
        return $this->element->getText();
    }

    public function cssProperty(string $cssProperty): string
    {
        return $this->element->getCSSValue($cssProperty);
    }
}
