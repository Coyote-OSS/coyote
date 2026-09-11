<?php
namespace Tests\Legacy\Integration\BaseFixture\Server;

use Tests\Legacy\Integration\BaseFixture\Constraint\UrlPathEquals;
use Tests\Legacy\Integration\BaseFixture\Server;

trait RelativeUri
{
    use Server\Http;

    function relativeUri(string $relativeUri): UrlPathEquals
    {
        return new UrlPathEquals($this->server->baseUrl, $relativeUri);
    }
}
