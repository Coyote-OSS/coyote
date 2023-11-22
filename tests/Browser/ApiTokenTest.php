<?php

namespace Tests\Browser;

use Coyote\User;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Dusk\Browser;

class ApiTokenTest extends DuskTestCase
{
    use WithFaker;

    public function testCreateToken()
    {
        $user = factory(User::class)->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser
                ->loginAs($user)
                ->visit('/User/Tokens')
                ->type('token', $token = $this->faker->word)
                ->press('Dodaj')
                ->waitForText('Twój nowy token')
                ->press('OK')
                ->assertSee($token);

            $browser->logout();
        });
    }
}
