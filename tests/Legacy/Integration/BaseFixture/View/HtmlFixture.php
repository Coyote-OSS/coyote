<?php
namespace Tests\Legacy\Integration\BaseFixture\View;

class HtmlFixture
{
    private ViewDom $dom;

    public function __construct(string $html)
    {
        $this->dom = new ViewDom($html);
    }

    public function metaDeclarations(): array
    {
        $declarations = [];

        /** @var \DOMElement $element */
        foreach ($this->dom->elements(xPath:'/html/head/meta') as $element) {
            $declarations[] = [
                'property' => $this->attribute($element, 'property'),
                'name'     => $this->attribute($element, 'name'),
                'content'  => $this->attribute($element, 'content'),
            ];
        }
        return $declarations;
    }

    private function attribute(\DOMElement $element, string $attribute): ?string
    {
        if ($element->hasAttribute($attribute)) {
            return $element->getAttribute($attribute);
        }
        return null;
    }
}
