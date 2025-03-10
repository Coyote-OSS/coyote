<?php
namespace Neon\Acceptance\Test\Dsl\Internal;

use Facebook\WebDriver\Remote\RemoteWebElement;

readonly class SeleniumElement
{
    public function __construct(private RemoteWebElement $element) {}

    public function text(): string
    {
        return $this->element->getText();
    }
}
