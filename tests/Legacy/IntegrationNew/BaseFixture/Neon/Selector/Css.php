<?php
namespace Tests\Legacy\IntegrationNew\BaseFixture\Neon\Selector;

readonly class Css implements \Stringable
{
    private string $xPath;

    public function __construct(private string $selector)
    {
        $this->xPath = $this->selectorXPath();
    }

    private function selectorXPath(): string
    {
        if ($this->isClassSelector()) {
            return $this->xPathByClass();
        }
        if ($this->isIdSelector()) {
            return $this->xPathById();
        }
        throw new \InvalidArgumentException();
    }

    private function isClassSelector(): bool
    {
        return \str_contains($this->selector, '.');
    }

    private function isIdSelector(): bool
    {
        return $this->selector[0] === '#';
    }

    private function xPathByClass(): string
    {
        [$tag, $class] = \explode('.', $this->selector);
        $tag = $tag ?: '*';
        return "{$tag}[@class and contains(concat(' ', normalize-space(@class), ' '), ' $class ')]";
    }

    private function xPathById(): string
    {
        $id = \subStr($this->selector, 1);
        return "*[@id='$id']";
    }

    public function __toString(): string
    {
        return $this->xPath;
    }
}
