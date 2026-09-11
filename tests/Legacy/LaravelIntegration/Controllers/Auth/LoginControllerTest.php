<?php

namespace Tests\Legacy\LaravelIntegration\Controllers\Auth;

use Coyote\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Legacy\LaravelIntegration\TestCase;

class LoginControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function testLoginFailedDueToBlockedUser()
    {
        $user = factory(User::class)->state('blocked')->create(['password' => bcrypt('123')]);

        $response = $this->json('POST', '/Login', ['name' => $user->name, 'password' => '123']);

        $response->assertJsonValidationErrors(['name']);
        $response->assertJsonFragment([
            'name' => ['Konto o tym loginie zostało zablokowane.']
        ]);
    }

    public function testLoginFailedDueToDeletedUser()
    {
        $user = factory(User::class)->state('deleted')->create(['password' => bcrypt('123')]);

        $response = $this->json('POST', '/Login', ['name' => $user->name, 'password' => '123']);

        $response->assertJsonValidationErrors(['name']);
        $response->assertJsonFragment([
            'name' => ['Użytkownik o podanej nazwie nie istnieje.']
        ]);
    }
}
