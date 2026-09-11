<?php
namespace Tests\Legacy\LaravelDusk;

use Coyote\User;
use Faker\Factory;
use Laravel\Dusk\Browser;

class ConfirmTest extends DuskTestCase
{
    public function tearDown(): void
    {
        parent::setUp();
        $this->browse(fn(Browser $browser) => $browser->script('window.localStorage.clear()'));
    }

    public function testSendVerificationEmail()
    {
        /** @var User $user */
        $user = factory(User::class)->create([
            'password'   => bcrypt('123'),
            'is_confirm' => false,
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser
                ->visit('/Confirm')
                ->click('#gdpr-all')
                ->waitUntilMissing('.gdpr-modal')
                ->type('email', $user->email)
                ->press('Wyślij e-mail z linkiem aktywacyjnym')
                ->assertSee('Na podany adres e-mail został wysłany link aktywacyjny.');
        });
    }

    public function testSendVerificationFailedToDueInvalidEmail()
    {
        $faker = Factory::create();

        $this->browse(function (Browser $browser) use ($faker) {
            $browser->visit('/Confirm')
                ->click('#gdpr-all')
                ->waitUntilMissing('.gdpr-modal')
                ->type('email', $faker->email)
                ->press('Wyślij e-mail z linkiem aktywacyjnym')
                ->assertSee('Podany adres e-mail nie istnieje.');
        });
    }

    public function testSendVerificationFailedToDuePreviousVerificationProcess()
    {
        /** @var User $user */
        $user = factory(User::class)->create(['password' => bcrypt('123')]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->visit('/Confirm')
                ->click('#gdpr-all')
                ->waitUntilMissing('.gdpr-modal')
                ->type('email', $user->email)
                ->press('Wyślij e-mail z linkiem aktywacyjnym')
                ->assertSee('Ten adres e-mail jest już zweryfikowany.');
        });
    }

    public function testSendVerificationEmailWithDifferentEmail()
    {
        /** @var User $user */
        $user = factory(User::class)->create(['password' => bcrypt('123'), 'is_confirm' => false, 'gdpr' => '{}']);
        $faker = Factory::create();

        $this->browse(function (Browser $browser) use ($user, $faker) {
            $browser
                ->loginAs($user)
                ->visit('/Confirm')
                ->assertInputValue('email', $user->email)
                ->type('email', $email = $faker->email)
                ->press('Wyślij e-mail z linkiem aktywacyjnym')
                ->assertSee('Na podany adres e-mail został wysłany link aktywacyjny.');

            $user->refresh();

            $this->assertEquals($email, $user->email);
        });
    }
}
