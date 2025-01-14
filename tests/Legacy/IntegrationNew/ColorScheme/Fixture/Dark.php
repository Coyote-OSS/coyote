<?php
namespace Tests\Legacy\IntegrationNew\ColorScheme\Fixture;

use Tests\Legacy\IntegrationNew\BaseFixture\View;
use Tests\Legacy\IntegrationNew\BaseFixture\View\ViewDom;

trait Dark
{
    use View\HtmlView;

    function isDark(): bool
    {
        $dom = new ViewDom($this->htmlView('/'));
        foreach ($dom->elements(xPath:'/html/body') as $canonical) {
            return \strPos($canonical->getAttribute('class'), 'theme-dark') !== false;
        }
        throw new \Exception();
    }

    function setDarkTheme(bool $dark): void
    {
        $this->laravel->post('/User/Settings/Ajax', ['colorScheme' => $dark ? 'dark' : 'light']);
    }

    function setLastColorScheme(string $scheme): void
    {
        $this->laravel->post('/User/Settings/Ajax', ['lastColorScheme' => $scheme]);
    }
}
