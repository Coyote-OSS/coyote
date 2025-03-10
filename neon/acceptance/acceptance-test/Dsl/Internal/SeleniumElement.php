<?php
namespace Neon\Acceptance\Test\Dsl\Internal;

use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriverBy;

readonly class SeleniumElement
{
    public function __construct(private RemoteWebElement $element) {}

    public function text(): string
    {
        return $this->element->getText();
    }

    public function fill(string $searchPhrase): void
    {
        $this->element->clear()->sendKeys($searchPhrase);
    }

    public function click(): void
    {
        $this->element->click();
    }

    public function byPlaceholder(string $placeholder): self
    {
        return new self($this->byCssSelector("[placeholder='$placeholder']"));
    }

    public function byTestId(string $testId): self
    {
        return new self($this->byCssSelector("[data-testid='$testId']"));
    }

    private function byCssSelector(string $cssSelector): RemoteWebElement
    {
        return $this->element->findElement(WebDriverBy::cssSelector($cssSelector));
    }
}
