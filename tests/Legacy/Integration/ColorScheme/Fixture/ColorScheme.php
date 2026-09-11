<?php
namespace Tests\Legacy\Integration\ColorScheme\Fixture;

use Tests\Legacy\Integration\BaseFixture\View;
use Tests\Legacy\Integration\BaseFixture\View\ViewDom;

trait ColorScheme
{
    use View\HtmlView;

    function colorScheme(): ?string
    {
        $dom = new ViewDom($this->htmlView('/'));
        foreach ($dom->elements(xPath:'/html/body') as $canonical) {
            return $canonical->getAttribute('data-color-scheme');
        }
        return null;
    }

    function setColorScheme(string $colorScheme): void
    {
        $this->laravel->post('/User/Settings/Ajax', ['colorScheme' => $colorScheme]);
    }

    function setColorSchemeLegacy(string $colorScheme): void
    {
        $this->laravel->post('/User/Settings/Ajax', ['dark.theme' => $colorScheme === 'dark']);
    }
}
