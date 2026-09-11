<?php
namespace Tests\Legacy\Integration\Seo\Schema\Fixture;

use Tests\Legacy\Integration\BaseFixture\View;
use Tests\Legacy\Integration\BaseFixture\View\JavaScriptFixture;

trait Schema
{
    use View\HtmlView;

    function schema(string $uri, string $type): ?array
    {
        return $this->firstSchema(new JavaScriptFixture($this->htmlView($uri)), $type);
    }

    function firstSchema(JavaScriptFixture $viewFixture, string $type): ?array
    {
        foreach ($this->schemaObjects($viewFixture) as $schema) {
            if ($schema['@type'] === $type) {
                return $schema;
            }
        }
        return null;
    }

    function schemaObjects(JavaScriptFixture $viewFixture): iterable
    {
        foreach ($viewFixture->scriptDeclarations() as $declaration) {
            if ($declaration->type() === 'application/ld+json') {
                yield $declaration->object();
            }
        }
    }
}
