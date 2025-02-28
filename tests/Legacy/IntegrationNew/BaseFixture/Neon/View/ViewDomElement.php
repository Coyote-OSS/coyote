<?php
namespace Tests\Legacy\IntegrationNew\BaseFixture\Neon\View;

readonly class ViewDomElement
{
    public function __construct(private \DOMElement $element)
    {
    }

    public function tagName(): string
    {
        return $this->element->tagName;
    }

    public function attribute(string $attribute): ?string
    {
        if ($this->element->hasAttribute($attribute)) {
            return $this->element->getAttribute($attribute);
        }
        return null;
    }

    public function hasAttribute(string $attribute): bool
    {
        return $this->element->hasAttribute($attribute);
    }

    public function child(string $tagName): ViewDomElement
    {
        $children = $this->element->getElementsByTagName($tagName);
        foreach ($children as $child) {
            return new ViewDomElement($child);
        }
        throw new \Exception("Failed to find child element: <$tagName>");
    }

    public function firstChild(): ViewDomElement
    {
        if ($this->element->firstChild === null) {
            throw new \Exception('Failed to find child element.');
        }
        return new ViewDomElement($this->element->firstElementChild);
    }
}
