<?php

namespace Tests\Legacy\LaravelIntegration\Controllers\Auth;

use Tests\Legacy\LaravelIntegration\TestCase;

class RegisterControllerTest extends TestCase
{
    public function testSubmitWithEmptyInput()
    {
        $response = $this->json('POST', '/Register');

        $response->assertJsonValidationErrors(['name', 'email', 'password']);
        $response->assertJsonFragment([
            'name' => ['Pole nazwa użytkownika jest wymagane.'],
            'email' => ['Pole email jest wymagane.'],
            'password' => ['Pole hasło jest wymagane.']
        ]);
    }

    public function testSubmitWithInvalidUsername()
    {
        $response = $this->json('POST', '/Register', ['name' => '^%&%%$^$']);

        $response->assertJsonValidationErrors(['name', 'email', 'password']);
        $response->assertJsonFragment([
            'name' => ['Nazwa użytkownika może zawierać litery, cyfry oraz znaki ._ -']
        ]);
    }
}
