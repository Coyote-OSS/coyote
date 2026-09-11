<?php
namespace Tests\Legacy\Integration\OAuth\Fixture;

use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Constraint\Constraint;
use Tests\Legacy\Integration\BaseFixture\Server;
use Tests\Legacy\Integration\OAuth\Fixture\Constraint\ResponseRedirect;

trait Assertion
{
    use Server\Http;

    function oAuthLogin(string $provider): TestResponse
    {
        return $this->server->get("/OAuth/$provider/Login");
    }

    function redirect(Constraint $constraint): Constraint
    {
        return new ResponseRedirect($constraint);
    }
}
