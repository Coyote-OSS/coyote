<?php

namespace Tests\Legacy\LaravelIntegration\Controllers\Auth;

use Coyote\User;
use Faker\Factory;
use Tests\Legacy\LaravelIntegration\TestCase;

class ForgotPasswordControllerTest extends TestCase
{
    public function testForgotPasswordCaseInsensitive()
    {
        $faker = Factory::create();
        $email = $faker->email;

        factory(User::class)->create(['is_confirm' => true, 'email' => strtoupper($email)]);

        $response = $this->json('POST', '/Password', ['email' => $email]);
        $response->assertStatus(302);
    }

    public function testSubmitFormWithDeletedUser()
    {
        $faker = Factory::create();
        $email = $faker->email;

        factory(User::class)->create(['is_confirm' => true, 'deleted_at' => now(), 'email' => $email]);

        $response = $this->json('POST', '/Password', ['email' => $email]);
        $response->assertStatus(422);

        $response->assertJsonValidationErrors('email');
        $errors = $response->json('errors.email');

        $this->assertEquals(trans('validation.email_exists'), $errors[0]);
    }

    public function testSubmitFormWithNotConfirmedEmail()
    {
        $faker = Factory::create();
        $email = $faker->email;

        factory(User::class)->create(['is_confirm' => false, 'email' => $email]);

        $response = $this->json('POST', '/Password', ['email' => $email]);
        $response->assertStatus(422);

        $response->assertJsonValidationErrors('email');
    }
}
