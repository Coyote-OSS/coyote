<?php
namespace Tests\Legacy\Integration\OpenGraph\Fixture;

use Tests\Legacy\Integration\BaseFixture\View;
use Tests\Legacy\Integration\BaseFixture\View\HtmlFixture;

trait OpenGraph
{
    use View\HtmlView;

    function metaProperty(string $property, string $uri): string
    {
        $html = new HtmlFixture($this->htmlView($uri));
        return $this->attributeByProperty($html, $property);
    }

    function attributeByProperty(HtmlFixture $html, string $property): mixed
    {
        foreach ($html->metaDeclarations() as $meta) {
            if ($meta['property'] === $property) {
                return $meta['content'];
            }
        }
        throw new \Exception("Failed to recognize in view meta property: $property");
    }
}
