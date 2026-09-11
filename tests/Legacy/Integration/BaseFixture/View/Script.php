<?php
namespace Tests\Legacy\Integration\BaseFixture\View;

class Script
{
    public function __construct(private \DOMElement $element)
    {
    }

    public function object(): array
    {
        return \json_decode($this->content(), true, flags:\JSON_THROW_ON_ERROR);
    }

    public function content(): string
    {
        return \trim($this->element->nodeValue);
    }

    public function type(): string
    {
        return $this->element->getAttribute('type');
    }
}
