<?php

namespace Tests\Legacy\LaravelDusk;

use Coyote\User;
use Faker\Factory;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;

class UserAccountTest extends DuskTestCase
{
    public function testDeleteAccountWithPassword()
    {
        $user = factory(User::class)->create(['password' => Hash::make('123'), 'gdpr' => '{}']);

        $this->browse(function (Browser $browser) use ($user) {
            $faker = Factory::create();

            $browser
                ->loginAs($user)
                ->visit('/User/DeleteAccount')
                ->press('Potwierdzam, że chcę usunąć moje konto')
                ->assertSee('Pole hasło jest wymagane.')
                ->type('password', $faker->password)
                ->press('Potwierdzam, że chcę usunąć moje konto')
                ->assertSee('Wprowadzone hasło nie jest poprawne.')
                ->type('password', '123')
                ->press('Potwierdzam, że chcę usunąć moje konto')
                ->assertSee('Konto zostało prawidłowo usunięte.');

            $browser->logout();
        });
    }

    public function testDeleteAccountWithoutPassword()
    {
        $user = factory(User::class)->create(['password' => null, 'gdpr' => '{}']);

        $this->browse(function (Browser $browser) use ($user) {
            $browser
                ->loginAs($user)
                ->visit('/User/DeleteAccount')
                ->assertMissing('input[type="password"]')
                ->press('Potwierdzam, że chcę usunąć moje konto')
                ->assertPathIs('/')
                ->assertSee('Konto zostało prawidłowo usunięte.');

            $browser->logout();
        });
    }
}
