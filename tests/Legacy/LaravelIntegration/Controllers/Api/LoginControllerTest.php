<?php

namespace Tests\Legacy\LaravelIntegration\Controllers\Api;

use Coyote\User;
use Illuminate\Support\Facades\Hash;
use Tests\Legacy\LaravelIntegration\TestCase;

class LoginControllerTest extends TestCase
{
    public function testApiLogin()
    {
        $password = Hash::make('123');
        $user = factory(User::class)->create(['password' => $password]);

        $response = $this->json('POST', '/v1/login', ['name' => $user->name, 'password' => '1234']);
        $response->assertJsonValidationErrors(['name']);

        $response = $this->json('POST', '/v1/login', ['name' => $user->name, 'password' => '123']);
        $this->assertEquals(200, $response->getStatusCode());

        $bearer = $response->getContent();

        $this->json('GET', '/v1/user')->assertStatus(401);

        $response = $this->json('GET', '/v1/user', [], ['Authorization' => 'Bearer ' . $bearer]);
        $response->assertJson(['name' => $user->name]);
    }
}
