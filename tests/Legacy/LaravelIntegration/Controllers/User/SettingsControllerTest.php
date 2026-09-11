<?php

namespace Tests\Legacy\LaravelIntegration\Controllers\User;

use Coyote\User;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Legacy\LaravelIntegration\TestCase;

class SettingsControllerTest extends TestCase
{
    use WithFaker;

    private User $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = factory(User::class)->create();
    }

    public function testSubmitLinkFailsToDueReputation()
    {
        $response = $this->actingAs($this->user)->json('POST', '/User/Settings', [
            'email'   => $this->user->email,
            'website' => $this->faker->url,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['website']);
        $response->assertJson([
            "message" => "Potrzebujesz minimum 50 punktów reputacji aby zmienić zawartość tego pola.",
            "errors"  => [
                "website" => ["Potrzebujesz minimum 50 punktów reputacji aby zmienić zawartość tego pola."],
            ],
        ]);
    }

    public function testSubmitLinkInSignatureFailsToDueReputation()
    {
        $response = $this->actingAs($this->user)->json('POST', '/User/Settings', [
            'email' => $this->user->email,
            'sig'   => 'Lorem ipsum: ' . $this->faker->url,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['sig']);
        $response->assertJson([
            "message" => "Potrzebujesz minimum 50 punktów reputacji, aby umieścić link w tym polu.",
            "errors"  => [
                "sig" => ["Potrzebujesz minimum 50 punktów reputacji, aby umieścić link w tym polu."],
            ],
        ]);
    }
}
