<?php
namespace Tests\Legacy\Integration\BaseFixture\View;

use Tests\Legacy\Integration\BaseFixture\Server;

trait HtmlView
{
    use Server\Http;

    function htmlView(string $uri): string
    {
        return $this->server->get($uri)->assertSuccessful()->content();
    }
}
