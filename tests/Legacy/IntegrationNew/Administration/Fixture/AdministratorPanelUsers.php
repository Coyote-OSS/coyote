<?php
namespace Tests\Legacy\IntegrationNew\Administration\Fixture;

use Illuminate\Testing\TestResponse;
use Tests\Legacy\IntegrationNew\BaseFixture\Neon\View\ViewDom;
use Tests\Legacy\IntegrationNew\BaseFixture\Server;

trait AdministratorPanelUsers
{
    use Server\Http;

    function userNames(TestResponse $response): array
    {
        $dom = new ViewDom($response->content());
        return $dom->findStrings('//table//tr/td[2]/a/text()');
    }

    function searchByUsername(string $username): TestResponse
    {
        return $this->server->get('/Adm/Users?name=' . $username);
    }
}
