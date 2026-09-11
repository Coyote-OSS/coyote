<?php
namespace Tests\Legacy\Integration\Seo\Meta\Fixture;

use Tests\Legacy\Integration\BaseFixture\View;
use Tests\Legacy\Integration\BaseFixture\View\HtmlFixture;

trait MetaProperty
{
    use View\HtmlView;

    function metaProperty(string $property, string $uri): string
    {
        $html = new HtmlFixture($this->htmlView($uri));
        return $this->attributeByName($html, $property);
    }

    function attributeByName(HtmlFixture $html, string $name): mixed
    {
        foreach ($html->metaDeclarations() as $meta) {
            if ($meta['name'] === $name) {
                return $meta['content'];
            }
        }
        throw new \Exception("Failed to recognize in view meta name: $name");
    }
}
